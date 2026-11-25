<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryQuestionTypes
{
    /**
     * Returns the schema of the editor question type list.
     *
     * @param SchemaContract ...$properties Unused, kept for the signature shared by the schema factories
     * @return Schema Schema of the categories and the question types keyed by theme name
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $categorySchema = Schema::object()
            ->description('Category of the question type selector')
            ->properties(
                Schema::string('name'),
                Schema::string('title'),
                Schema::string('icon')
            );

        $questionTypeSchema = Schema::object()
            ->description('Question theme merged with its editor settings from react/questiontypes.xml')
            ->properties(
                Schema::string('name'),
                Schema::string('type'),
                Schema::string('title'),
                Schema::string('group'),
                Schema::string('extends'),
                Schema::boolean('coreTheme'),
                Schema::boolean('visible'),
                Schema::object('settings'),
                Schema::boolean('editorOnly'),
                Schema::string('category')->nullable(),
                Schema::integer('sortOrder')->nullable(),
                Schema::boolean('devOnly')
            );

        return Schema::create()->title('Question Types')
            ->description('Question types of the editor: Selector categories, and all question themes keyed by theme name')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::boolean('success'),
                Schema::array('categories')->items($categorySchema),
                Schema::object('questionTypes')->additionalProperties($questionTypeSchema)
            );
    }
}
