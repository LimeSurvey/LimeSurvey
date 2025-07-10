<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryI18n
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('I18nTranslationsAndLanguages')
            ->description('Internationalization Translations and Languages')
            ->type(Schema::TYPE_ARRAY)
            ->items(
                Schema::object()->properties(
                    Schema::object('translations')
                        ->additionalProperties(Schema::string()),
                    Schema::object('languages')
                        ->additionalProperties(
                            Schema::object()->properties(
                                Schema::string('description'),
                                Schema::string('nativedescription'),
                                Schema::boolean('rtl'),
                                Schema::integer('dateformat'),
                                Schema::integer('radixpoint'),
                                Schema::string('momentjs')
                            )
                        )
                )
            );
    }
}
