<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryAnswer
{
    public function create() : Schema
    {
        return Schema::create()
            ->title('Answer')
            ->description('Answer')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('aid')->default(null),
                Schema::integer('qid')->default(null),
                Schema::string('code')->default(null),
                Schema::integer('sortorder')->default(null),
                Schema::integer('assessment_value')->default(null),
                Schema::integer('scale_id')->default(null)
            );
    }
}
