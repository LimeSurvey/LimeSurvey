<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyGroups
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('Survey groups')
            ->description('Survey groups')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('surveyGroups')->items(
                    AllOf::create()->schemas(
                        (new SchemaFactorySurveyGroup())->make()
                    )
                )
            );
    }
}
