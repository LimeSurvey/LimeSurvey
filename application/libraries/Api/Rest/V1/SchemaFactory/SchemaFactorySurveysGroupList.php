<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveysGroupList
{
    public function make(): Schema
    {
        return Schema::create()
            ->title('Surveys Group List')
            ->description('Surveys Group List')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('surveysGroups')->items(
                    AllOf::create()->schemas(
                        (new SchemaFactorySurveysGroup())->make()
                    )
                )
            );
    }
}