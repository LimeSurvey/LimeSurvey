<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryQuestionThemeAttributes
{
    /**
     * @param SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $attributeSchema = Schema::object()
            ->description('Attribute definition: config.xml attribute merged with the editor overlay')
            ->properties(
                Schema::string('name'),
                Schema::string('tab'),
                Schema::string('attributePath'),
                Schema::boolean('languageBased'),
                Schema::string('caption'),
                Schema::string('component'),
                Schema::string('inputtype'),
                Schema::integer('sortorder'),
                Schema::integer('simpleOrder')
            );

        $themeSchema = Schema::object()
            ->properties(
                Schema::object('attributes')->additionalProperties($attributeSchema)
            );

        return Schema::create()->title('Question Theme Attributes')
            ->description('Editor attribute definitions of all question themes, keyed by theme name')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::boolean('success'),
                Schema::object('attributesDetails')->additionalProperties($themeSchema)
            );
    }
}
