<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Models\Services\FileUploadService;

class FileUpload implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected FileUploadService $fileUploadService;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     * @param FileUploadService $fileUploadService
     */
    public function __construct(
        ResponseFactory $responseFactory,
        FileUploadService $fileUploadService
    ) {
        $this->responseFactory = $responseFactory;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * @param Request $request
     * @return Response
     * @psalm-suppress PossiblyNullArgument
     */
    public function run(Request $request)
    {
        try {
            $surveyId = (int)$request->getData('_id');
            $returnedData = $this->fileUploadService->storeSurveyImage(
                $surveyId,
                $request->getData('filesGlobal', [])
            );
        } catch (\Exception $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess($returnedData);
    }
}
