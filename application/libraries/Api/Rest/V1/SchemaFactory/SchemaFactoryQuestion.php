<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryQuestion
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('Question')
            ->description('Question')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('qid')->default(null),
                Schema::integer('parentQid')->default(0),
                Schema::integer('sid')->default(null),
                Schema::string('type')->default(null),
                Schema::string('title')->default(null),
                Schema::string('preg')->default(null),
                Schema::boolean('other')->default(null),
                Schema::boolean('mandatory')->default(null),
                Schema::boolean('encrypted')->default(null),
                Schema::integer('questionOrder')->default(0),
                Schema::integer('scaleId')->default(null),
                Schema::boolean('sameDefault')->default(null),
                Schema::boolean('mandatory')->default(null),
                Schema::string('questionThemeName')->default(null),
                Schema::string('moduleName')->default(null),
                Schema::integer('gid')->default(0),
                Schema::string('relevance')->default(null),
                Schema::string('sameScript')->default(null),
                Schema::boolean('mandatory')->default(null),
                Schema::create('l10ns')
                    ->additionalProperties(
                        (new SchemaFactoryQuestionL10ns())->make()
                    ),
                ...$properties
            );
    }
}
