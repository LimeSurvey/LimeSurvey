<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    Exception
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use DI\FactoryInterface;
use LimeSurvey\Models\Services\FileUploadService;
use Yii;

class FileUpload implements CommandInterface
{
    use AuthPermissionTrait;

    protected FactoryInterface $diFactory;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->diFactory = $diFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $surveyId = (int) $request->getData('_id');
            $diContainer = \LimeSurvey\DI::getContainer();
            $fileUploadService = $diContainer->get(
                FileUploadService::class
            );
            $returnedData = $fileUploadService->storeSurveyImage($surveyId, $_FILES);
        } catch (Exception $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess($returnedData);
    }

}
