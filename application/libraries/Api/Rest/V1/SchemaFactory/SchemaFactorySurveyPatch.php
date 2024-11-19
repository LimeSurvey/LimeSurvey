<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\{
    Schema,
    AllOf,
    AnyOf
};

class SchemaFactorySurveyPatch
{
    public function make(): Schema
    {
        $props = [
            Schema::array('patch')->items(
                AllOf::create()->schemas(
                    Schema::object()
                    ->properties(
                        Schema::string('entity')->enum(
                            'survey',
                            'languageSetting',
                            'questionGroup',
                            'questionGroupL10n',
                            'question',
                            'questionL10n',
                            'questionAnswer',
                            'questionAttribute'
                        ),
                        Schema::string('op')->enum(
                            'update',
                            'delete',
                            'create'
                        ),
                        AnyOf::create('id')
                            ->schemas(
                                Schema::string(),
                                Schema::object()
                            ),
                        Schema::object('props')
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
