<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveyGroupList
{
    public function make(): Schema
    {
        return Schema::create()
            ->title('Survey Group List')
            ->description('Survey Group List')
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
