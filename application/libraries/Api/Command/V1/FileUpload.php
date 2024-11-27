<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use DI\FactoryInterface;
use LimeSurvey\Models\Services\FileUploadService;

class FileUpload implements CommandInterface
{
    use AuthPermissionTrait;

    protected FactoryInterface $diFactory;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @return Response
     * @psalm-suppress PossiblyUndefinedVariable
     */
    public function run(Request $request)
    {
        try {
            $surveyId = (int)$request->getData('_id');
            $diContainer = \LimeSurvey\DI::getContainer();
            $fileUploadService = $diContainer->get(
                FileUploadService::class
            );
            $returnedData = $fileUploadService->storeSurveyImage(
                $surveyId,
                $_FILES
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
