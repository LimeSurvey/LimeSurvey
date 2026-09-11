<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyLogic
{
    /**
     * @param SchemaContract ...$properties Additional properties to add to the schema
     * @return Schema The complete schema definition
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $surveyLogicSchema = Schema::object('surveyLogic')
            ->properties(
                Schema::string('html')->description('Rendered survey logic overview HTML'),
                Schema::integer('errors')->default(0)->description('Total number of logic errors found')
            );

        return Schema::create()
            ->title('Survey Logic')
            ->description('Survey logic overview')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                $surveyLogicSchema,
                ...$properties
            );
    }
}
