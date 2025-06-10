<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

/**
 * Schema factory for personal settings patch
 */
class SchemaFactoryPersonalSettingsPatch
{
    /**
     * Create the schema
     *
     * @return Schema
     */
    public function make(): Schema
    {
        return Schema::create()
            ->title('Personal Settings Patch')
            ->description('Personal Settings Patch')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::boolean('showQuestionCodes')
                    ->description('Show question codes preference')
            );
    }
}