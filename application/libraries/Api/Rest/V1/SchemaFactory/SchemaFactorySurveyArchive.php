<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurveyArchive
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $schemaSurveyLanguageSettings = (new SchemaFactorySurveyLanguageSettings())->make();
        $schemaSurveyDefaultLanguage = Schema::object('defaultlanguage')
            ->properties(...$schemaSurveyLanguageSettings->properties);

        return Schema::create()
            ->title('Survey')
            ->description('Survey')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('timestamp'),
                Schema::integer('count')->default(0),
                Schema::integer('newformat'),
                Schema::array('types'),
                Schema::boolean('hastokens'),
                Schema::string('alias'),
                $schemaSurveyDefaultLanguage,
                ...$properties
            );
    }
}
