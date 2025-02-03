<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\{
    Schema,
    AllOf
};

class SchemaFactoryI18nMissingTranslations
{
    public function make(): Schema
    {
        $props = [
            Schema::array('keys')->items(
                AllOf::create()->schemas(
                    Schema::object()
                        ->properties(
                            Schema::string('key')
                                ->description('Text to be translated'),
                            Schema::string('lang')
                                ->description('Language code')
                        )
                        ->required('key', 'lang')
                )
            )
        ];

        return Schema::create()
            ->title('I18n Missing Translations')
            ->description('Schema for missing translations')
            ->type(Schema::TYPE_OBJECT)
            ->properties(...$props);
    }
}