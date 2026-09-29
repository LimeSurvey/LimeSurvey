<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyGroup
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('Survey Group')
            ->description('Survey Group')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('gsid')->default(null),
                Schema::string('name')->default(null),
                Schema::string('description')->default(null),
                Schema::string('title')->default(null),
                Schema::integer('sortOrder')->default(null),
                Schema::boolean('alwaysAvailable')->default(null),
            );
    }
}
