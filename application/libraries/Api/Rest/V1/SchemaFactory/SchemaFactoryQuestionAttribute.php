<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryQuestionAttribute
{
    public function make(): Schema
    {
        return Schema::create()
            ->title('Question Attribute')
            ->description('Question Attribute')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('qaid')->default(null),
                Schema::integer('attribute')->default(0),
                Schema::string('value')->default(null),
                Schema::string('language')->default('')
            );
    }
}
