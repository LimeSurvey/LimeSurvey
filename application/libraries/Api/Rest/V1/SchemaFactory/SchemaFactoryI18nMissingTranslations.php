<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryI18nMissingTranslations
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('I18nMissingTranslations')
            ->description('Schema for saving missing translations')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::string('message')
                    ->description('Success message with the saved key')
                    ->example('Translation key saved: key')
            );
    }
}
