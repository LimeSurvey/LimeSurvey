<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryExpressionScriptValidationRequest
{
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('Expression Script validation request')
            ->description('Expression and question context to validate')
            ->type(Schema::TYPE_OBJECT)
            ->required('expression', 'questionId')
            ->properties(
                Schema::string('expression'),
                Schema::integer('questionId'),
                ...$properties
            );
    }
}
