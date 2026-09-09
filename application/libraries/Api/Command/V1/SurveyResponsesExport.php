<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use Exception;
use InvalidArgumentException;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use Permission;
use RuntimeException;
use Survey;
use Yii;

class SurveyResponsesExport implements CommandInterface
{
    use AuthPermissionTrait;

    /**
     * Survey model service used to fetch survey records.
     *
     * @var Survey
     */
    protected Survey $survey;

    /**
     * The loaded Survey instance for the current request.
     *
     * @var Survey|null
     */
    protected $surveyModel;

    /**
     * Factory used to build API responses.
     *
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * Permission helper used to check survey permissions.
     *
     * @var Permission
     */
    protected Permission $permission;

    /**
     * Service responsible for exporting survey results.
     *
     * @var ExportSurveyResultsService
     */
    protected ExportSurveyResultsService $exportSurvey;

    /**
     * Allowed export formats.
     * 'csv' and 'html' are handled by the fast, chunked ExportSurveyResultsService.
     * The rest are delegated to the legacy plugin-based writers (see legacyFormatMap).
     *
     * @var string[]
     */
    private array $allowedFormats = [
        'csv', 'html', 'pdf', 'excel', 'word', 'json', 'spss', 'stata', 'r_syntax', 'r_data',
    ];

    /**
     * Maps API format keys to the legacy export plugin keys registered via the
     * 'listExportPlugins' event (Authdb core plugin, ExportSPSSsav, ExportSTATAxml, ExportR).
     *
     * @var array<string, string>
     */
    private array $legacyFormatMap = [
        'pdf' => 'pdf',
        'excel' => 'xls',
        'word' => 'doc',
        'json' => 'json',
        'spss' => 'spsssav',
        'stata' => 'stataxml',
        'r_syntax' => 'rsyntax',
        'r_data' => 'rdata',
    ];

    /**
     * File extension and MIME type per legacy plugin key.
     *
     * @var array<string, array{extension: string, mimeType: string}>
     */
    private array $legacyFormatMeta = [
        'pdf' => ['extension' => 'pdf', 'mimeType' => 'application/pdf'],
        'xls' => ['extension' => 'xls', 'mimeType' => 'application/vnd.ms-excel'],
        'doc' => ['extension' => 'doc', 'mimeType' => 'application/msword'],
        'json' => ['extension' => 'json', 'mimeType' => 'application/json'],
        'spsssav' => ['extension' => 'sav', 'mimeType' => 'application/octet-stream'],
        'stataxml' => ['extension' => 'xml', 'mimeType' => 'application/xml'],
        'rsyntax' => ['extension' => 'R', 'mimeType' => 'text/plain'],
        'rdata' => ['extension' => 'dat', 'mimeType' => 'text/plain'],
    ];

    /**
     * Export formats handled by the fast chunked ExportSurveyResultsService.
     *
     * @var string[]
     */
    private array $nativeFormats = ['csv', 'html'];

    /**
     * Valid answer format values.
     *
     * @var string[]
     */
    private array $validAnswerFormats = ['long', 'short'];

    /**
     * Valid CSV field separators.
     *
     * @var string[]
     */
    private array $validCsvSeparators = [',', ';', "\t"];

    /**
     * SurveyResponsesExport constructor.
     *
     * @param Survey $survey Survey model/service used to fetch surveys
     * @param Permission $permission Permission helper for checking access
     * @param ResponseFactory $responseFactory Factory to create API responses
     * @param ExportSurveyResultsService $exportSurvey Service responsible for exporting survey results
     */
    public function __construct(
        Survey $survey,
        Permission $permission,
        ResponseFactory $responseFactory,
        ExportSurveyResultsService $exportSurvey
    ) {
        $this->permission = $permission;
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
        $this->exportSurvey = $exportSurvey;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $exportData = $this->process($request);
            $this->streamFile($exportData);
            return $this->responseFactory->makeSuccess();
        } catch (TransformerException $e) {
            return $this->responseFactory->makeError('Invalid key sent');
        } catch (PermissionDeniedException $e) {
            return $this->responseFactory->makeErrorUnauthorised();
        } catch (Exception $e) {
            return $this->responseFactory->makeError($e->getMessage());
        }
    }

    /**
     * Process the export request and perform the export.
     *
     * @param Request $request
     * @return array The export data with content/filePath and metadata
     *
     * @throws PermissionDeniedException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function process(Request $request): array
    {
        $surveyId = (int)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses', 'export')) {
            throw new PermissionDeniedException();
        }

        $this->surveyModel = $this->survey->findByPk($surveyId);
        if ($this->surveyModel === null) {
            throw new RuntimeException('Survey not found');
        }

        if (!$this->surveyModel->isActive) {
            throw new RuntimeException('Survey is not active - no responses are available.');
        }

        [$type, $language, $answerFormat, $csvSeparator] = $this->getExportRequestData($request);

        if (!in_array($type, $this->nativeFormats)) {
            return $this->exportViaLegacyExporter($surveyId, $type, $language, $answerFormat, $csvSeparator);
        }

        $exportService = $this->exportSurvey
            ->setLanguage($language)
            ->setOutputMode('file');

        if ($answerFormat) {
            $exportService->setAnswerFormat($answerFormat);
        }

        if ($csvSeparator) {
            $exportService->setCsvSeparator($csvSeparator);
        }

        return $exportService->exportResponses($surveyId, $type);
    }

    /**
     * Export via the legacy plugin-based writer system (Authdb, ExportSPSSsav,
     * ExportSTATAxml, ExportR core plugins), for formats not yet reimplemented
     * in ExportSurveyResultsService.
     *
     * @param int $surveyId
     * @param string $type
     * @param string|null $language
     * @param string|null $answerFormat
     * @param string|null $csvSeparator
     * @return array The export data with filePath/filename/mimeType
     *
     * @throws RuntimeException
     */
    protected function exportViaLegacyExporter(
        int $surveyId,
        string $type,
        ?string $language,
        ?string $answerFormat,
        ?string $csvSeparator
    ): array {
        Yii::app()->loadHelper('admin.exportresults');

        $legacyType = $this->legacyFormatMap[$type] ?? $type;

        $fieldMap = createFieldMap($this->surveyModel, 'full', true, false, $language);
        if ($this->surveyModel->savetimings === 'Y') {
            $fieldMap += createTimingsFieldMap($surveyId, 'full', true, false, $language);
        }

        $options = new \FormattingOptions();
        $options->selectedColumns = array_keys($fieldMap);
        $options->responseMinRecord = \SurveyDynamic::model($surveyId)->getMinId();
        $options->responseMaxRecord = \SurveyDynamic::model($surveyId)->getMaxId();
        $options->responseCompletionState = 'all';
        $options->headingFormat = 'full';
        $options->answerFormat = $answerFormat ?: 'long';
        $options->csvFieldSeparator = $csvSeparator ?: ',';
        $options->output = 'file';

        $legacyService = new \ExportSurveyResultsService();
        $filePath = $legacyService->exportResponses($surveyId, $language, $legacyType, $options);

        if (!$filePath || !file_exists($filePath)) {
            throw new RuntimeException('Export format not available: ' . $type);
        }

        $meta = $this->legacyFormatMeta[$legacyType] ?? ['extension' => 'dat', 'mimeType' => 'application/octet-stream'];

        return [
            'filePath' => $filePath,
            'filename' => 'responses_' . $surveyId . '.' . $meta['extension'],
            'mimeType' => $meta['mimeType'],
        ];
    }

    /**
     * Read and validate export-related parameters from the request.
     *
     * @param Request $request
     * @return array [type, language, answerFormat, csvSeparator]
     * @throws InvalidArgumentException
     */
    protected function getExportRequestData(Request $request): array
    {
        $type = $request->getData('type', 'csv');
        if (!in_array($type, $this->allowedFormats)) {
            throw new InvalidArgumentException('Invalid export format specified');
        }

        $language = $request->getData('language', $this->surveyModel ? $this->surveyModel->language : null);

        // Optional answer format (long/short)
        $answerFormat = $request->getData('answerFormat', null);
        if ($answerFormat && !in_array($answerFormat, $this->validAnswerFormats)) {
            throw new InvalidArgumentException('Invalid answer format specified');
        }

        // Optional CSV field separator
        $csvSeparator = $request->getData('csvSeparator', null);
        if ($csvSeparator && !in_array($csvSeparator, $this->validCsvSeparators)) {
            throw new InvalidArgumentException('Invalid CSV field separator specified');
        }

        return [$type, $language, $answerFormat, $csvSeparator];
    }

    /**
     * Stream the exported file for download.
     *
     * @param array $exportData Export data from the export service
     * @return never
     */
    protected function streamFile(array $exportData)
    {
        $filename = $exportData['filename'] ?? 'export.' . ($exportData['extension'] ?? 'csv');
        $mimeType = $exportData['mimeType'] ?? 'application/octet-stream';
        $content = $exportData['content'] ?? null;
        $filePath = $exportData['filePath'] ?? null;

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        if ($filePath !== null && file_exists($filePath)) {
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            unlink($filePath);
        } elseif ($content !== null) {
            header('Content-Length: ' . strlen($content));
            echo $content;
        }

        exit;
    }
}
