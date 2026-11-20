<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\FileUploadService;

/**
 * Uploads an image that is inserted into a text, e.g. with the HTML editor of the question text.
 */
class FileUploadContentImage implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected FileUploadService $fileUploadService;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory Creates the responses
     * @param FileUploadService $fileUploadService Validates and saves the image
     */
    public function __construct(
        ResponseFactory $responseFactory,
        FileUploadService $fileUploadService
    ) {
        $this->responseFactory = $responseFactory;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Validates the uploaded image and saves it in the survey's images folder.
     *
     * @param Request $request The request, with the survey ID in "_id" and the uploaded file in "filesGlobal"
     * @return Response The upload result, see FileUploadService::storeContentImage()
     */
    public function run(Request $request)
    {
        try {
            $returnedData = $this->fileUploadService->storeContentImage(
                (int) $request->getData('_id'),
                $request->getData('filesGlobal', [])
            );
        } catch (PermissionDeniedException $e) {
            return $this->responseFactory->makeErrorForbidden();
        } catch (\Exception $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory->makeSuccess($returnedData);
    }
}
