<?php

namespace LimeSurvey\Api\Rest\V2\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;

class SchemaFactorySurveyPatch
{
    public function create(): Schema
    {
        $props = [
            Schema::array('patch')->items(
                AllOf::create()->schemas(
                    Schema::object()
                    ->properties(
                        Schema::string('op'),
                        Schema::string('path'),
                        OneOf::create('value')->schemas(
                            Schema::string(),
                            Schema::number(),
                            Schema::integer(),
                            Schema::object(),
                            Schema::array()
                        )
                    )
                )
            )
        ];

        return Schema::create()
            ->title('Survey Patch')
            ->description('Survey Patch')
            ->type(Schema::TYPE_OBJECT)
            ->properties(...$props);
    }
}
