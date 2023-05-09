<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveyList
{
    public function create(): Schema
    {
        return Schema::create()
            ->title('Survey List')
            ->description('Survey List')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('surveys')->items(
            AllOf::create()->schemas(
                        (new SchemaFactorySurvey)->create()
                    )
                )
            );
    }
}
