<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryI18nTranslations
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('I18nTranslations')
            ->description('Internationalization Translations')
            ->type(Schema::TYPE_ARRAY)
            ->items(
                Schema::object()->additionalProperties(
                    Schema::string()
                )
            );
    }
}
