<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyQuestionsFieldname
{
    public function make(): Schema
    {
        $questionItemSchema = Schema::object()
            ->properties(
                Schema::string('fieldname'),
                Schema::integer('sid'),
                Schema::integer('gid'),
                Schema::integer('qid'),
                Schema::integer('sqid')->nullable(),
                Schema::string('aid')->nullable(),
                Schema::string('title'),
                Schema::integer('scale_id')->nullable(),
            );

        return Schema::create()
            ->title('Survey questions Fieldname')
            ->description('Survey questions with their fieldname and properties')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::object()->additionalProperties(
                    Schema::array()->items($questionItemSchema)
                )
            );
    }
}
