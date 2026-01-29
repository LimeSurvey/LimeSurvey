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
     *
     * @var string[]
     */
    private array $allowedFormats = ['csv', 'html'];

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

        [$type, $language] = $this->getExportRequestData($request);

        // Use file output mode for better memory efficiency with large exports
        return $this->exportSurvey->exportResponses($surveyId, $type, $language, 'file');
    }

    /**
     * Read and validate export-related parameters from the request.
     *
     * @param Request $request
     * @return array [type, language]
     * @throws InvalidArgumentException
     */
    protected function getExportRequestData(Request $request): array
    {
        $type = $request->getData('type', 'csv');
        if (!in_array($type, $this->allowedFormats)) {
            throw new InvalidArgumentException('Invalid export format specified');
        }

        $language = $request->getData('language', $this->surveyModel ? $this->surveyModel->language : null);

        return [$type, $language];
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
