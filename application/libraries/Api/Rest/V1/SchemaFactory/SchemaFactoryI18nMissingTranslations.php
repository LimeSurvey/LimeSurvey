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
            Schema::array('keys')
                ->items(Schema::string())
                ->description('Array of missing translation strings')
                ->example(['missingTranslationString1', 'missingTranslationString2'])
        ];

        return Schema::create()
            ->title('I18n Missing Translations')
            ->description('Schema for missing translations')
            ->type(Schema::TYPE_OBJECT)
            ->properties(...$props);
    }
}
