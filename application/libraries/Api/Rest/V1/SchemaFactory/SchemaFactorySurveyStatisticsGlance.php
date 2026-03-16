<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyStatisticsGlance
{
    /**
     * Create the OpenAPI schema for survey statistics overview
     *
     * @param SchemaContract ...$properties Additional properties to add to the schema
     * @return Schema The complete schema definition
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $statisticsSchema = Schema::object('statistics')
            ->properties(
                Schema::integer('totalResponses')->default(0),
                Schema::integer('incompleteResponses')->default(0),
                Schema::string('completionRate')->default('0.00'),
                Schema::string('avgCompletionTime')->default('0.0000')
            );

        return Schema::create()
            ->title('Survey Statistics Overview')
            ->description('Survey Statistics Overview')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                $statisticsSchema,
                ...$properties
            );
    }
}
