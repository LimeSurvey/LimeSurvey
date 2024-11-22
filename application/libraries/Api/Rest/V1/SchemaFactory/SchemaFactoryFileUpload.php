<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryFileUpload
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::object()
            ->title('File Upload')
            ->description('File Upload via multipart/form-data')
            ->properties(
                Schema::string('file')
                    ->description('The file to upload')
                    ->type('file')
                    ->format(Schema::FORMAT_BINARY) // Binary file
            )
            ->required('file'); // File is required
    }
}
