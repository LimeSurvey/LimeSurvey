<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyStatistics
{
    /**
     * Create the OpenAPI schema for survey statistics overview
     *
     * @param SchemaContract ...$properties Additional properties to add to the schema
     * @return Schema The complete schema definition
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $chartDataPoint = Schema::object()
            ->properties(
                Schema::string('key')->description('Unique identifier for the data point'),
                Schema::string('title')->description('Display title for the data point'),
                Schema::integer('value')->description('Numeric value/count for the data point')
            );

        $statisticsItemSchema = Schema::object()
            ->properties(
                Schema::string('title')->description('Title of the statistics item'),
                Schema::array('legend')
                    ->items(Schema::string())
                    ->description('Labels for the data points'),
                Schema::array('data')
                    ->items($chartDataPoint)
                    ->description('Statistical data points'),
                Schema::integer('total')
                    ->description('Total count if applicable')
                    ->nullable(),
                Schema::object('meta')
                    ->description('Additional metadata about the statistics')
            );

        return Schema::create()
        ->type(Schema::TYPE_OBJECT)
        ->properties(
            Schema::array('statistics')
                ->items(
                    $statisticsItemSchema,
                    ...$properties
                )
                ->description('Array of statistical data items for each question/metric')
        );
    }
}
