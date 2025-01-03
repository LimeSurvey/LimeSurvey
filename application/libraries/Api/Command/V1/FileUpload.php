<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\FileAggregateService;
use LimeSurvey\Models\Services\FileAggregateService\FileValidation\FileValidationService;
use Yii;
use Permission;

class FileUpload implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    private FileValidationService $fileValidationService;

    private Permission $permission;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory,
        FileValidationService $fileValidationService,
        Permission $permission
    )
    {
        $this->responseFactory = $responseFactory;
        $this->fileValidationService = $fileValidationService;
        $this->permission = $permission;
    }

    public function run(Request $request)
    {

        $file = \CUploadedFile::getInstanceByName('file');
        if($file === null) {
            return $this->responseFactory
                ->makeError([
                    'message' => 'File upload failed'
                ]);
        }

        $requestData = $this->dataFormatter($request, $file);

        $errors = $this->fileValidationService->validate($requestData);

        if (!empty($errors)) {
            return $this->responseFactory
                ->makeError([
                    'message' => 'File upload failed due to: ',
                    'errors' => $errors
                ]);
        }

        $fileService = DI::getContainer()->get(
            FileAggregateService::class
        );

        if ($fileService->upload($file)) {
            return $this->responseFactory
                ->makeSuccess([
                    'message' => 'File uploaded successfully'
                ]);
        } else {
            return $this->responseFactory
                ->makeError([
                    'message' => 'File upload failed'
                ]);
        }
    }

    private function dataFormatter($request, $file)
    {
        return [
            'survey_id' => $request->getData('survey_id'),
            'file' => $file
        ];
    }
}
