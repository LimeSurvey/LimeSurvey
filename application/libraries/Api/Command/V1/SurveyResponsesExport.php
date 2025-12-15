<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use Exception;
use InvalidArgumentException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use Permission;
use RuntimeException;
use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

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
     * @var Survey
     */
    protected Survey $surveyModel;

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
     * Formatting options that control export behaviour (CSV separator, selected columns, etc.).
     *
     * @var FormattingOptions
     */
    protected FormattingOptions $formattingOptions;


    /**
     * Allowed export formats.
     *
     * @var string[]
     */
    private array $allowedFormats = ['csv', 'xlsx', 'xls', 'pdf', 'html'];


    /**
     * SurveyResponsesExport constructor.
     *
     * @param Survey $survey Survey model/service used to fetch surveys
     * @param Permission $permission Permission helper for checking access
     * @param ResponseFactory $responseFactory Factory to create API responses
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
        // No DI for ExportSurveyResultsService and FormattingOptions yet
        $this->exportSurvey = $exportSurvey;
        $this->formattingOptions = new FormattingOptions();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $data = $this->process($request);

            return $this->responseFactory->makeSuccess(['responses' => $data]);
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
     * @return array The exported responses (format depends on ExportSurveyResultsService)
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

        return $this->exportSurvey->exportResponses($surveyId, $language, $type, $this->formattingOptions);
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
        // Correct the in_array check: ensure we throw if the requested type is not in allowedFormats
        if (!in_array($type, $this->allowedFormats)) {
            throw new InvalidArgumentException('Invalid export format specified');
        }

        $this->formattingOptions->csvFieldSeparator = $request->getData('separator', ',');
        $this->formattingOptions->selectedColumns = $request->getData('columns', '');

        $language = $request->getData('language', $this->surveyModel->language);
        $this->formattingOptions->output = 'display';

        return compact('type', 'language');
    }
}
