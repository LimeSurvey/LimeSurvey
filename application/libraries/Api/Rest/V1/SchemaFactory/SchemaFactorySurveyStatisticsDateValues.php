<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyStatisticsDateValues
{
    /**
     * Create the OpenAPI schema for the values of a Date/time question
     *
     * @return Schema The complete schema definition
     */
    public function make(): Schema
    {
        return Schema::object()
            ->properties(
                Schema::boolean('hasTime')
                    ->description('Whether the question records a time'),
                Schema::integer('total')
                    ->description('Number of answers with a valid date'),
                Schema::object('days')
                    ->description(
                        'Answers per day (Y-m-d) and time (H:i:s, empty when no time was entered), e.g. {"2026-01-12": {"09:00:00": 12}}'
                    )
            );
    }
}
