<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryI18NRefreshLanguages
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('I18nTranslations')
            ->description('Internationalization Translations')
            ->type(Schema::TYPE_OBJECT)
            ->additionalProperties(
                Schema::object()
                    ->properties(
                        Schema::string('description'),
                        Schema::string('nativedescription'),
                        Schema::boolean('rtl'),
                        Schema::integer('dateformat'),
                        Schema::integer('radixpoint'),
                        Schema::string('momentjs')
                    )
            );
    }
}
