<?php

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use LimeSurvey\Api\Command\V1\FileUpload;
use LimeSurvey\Api\Command\V1\FileUploadContentImage;
use LimeSurvey\Api\Rest\V1\SchemaFactory\SchemaFactoryError;

$errorSchema = (new SchemaFactoryError())->make();
$fileUploadSchema = Schema::object()
    ->properties(
        Schema::string('file')->format('binary')->description('The file to upload')
    );

$rest = [];

$rest['v1/file-upload-survey-image/$id'] = [
    'POST' => [
        'tag' => 'upload',
        'multipart' => true,
        'description' => 'File upload via  Axios post request (multipart/form-data)',
        'commandClass' => FileUpload::class,
        'params' => [
            'file' => ['src' => 'files'],
        ],
        'schema' => $fileUploadSchema,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'examples' => null,
                'content' => null,
                'schema' => null
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ],
];

$rest['v1/file-upload-content-image/$id'] = [
    'POST' => [
        'tag' => 'upload',
        'multipart' => true,
        'description' => 'Upload of an image inserted into a text, e.g. the question text (PNG, JPEG or WebP, max 10 MB). '
            . 'Metadata like EXIF is removed. The image is saved in the survey\'s images folder.',
        'commandClass' => FileUploadContentImage::class,
        'params' => [
            'file' => ['src' => 'files'],
        ],
        'schema' => $fileUploadSchema,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success. "success" is false and "uploadResultMessage" names the problem if the '
                    . 'image was rejected.',
                'examples' => null,
                'content' => null,
                'schema' => null
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ],
            'forbidden' => [
                'code' => 403,
                'description' => 'Forbidden',
                'schema' => $errorSchema
            ],
        ]
    ],
];

return $rest;
