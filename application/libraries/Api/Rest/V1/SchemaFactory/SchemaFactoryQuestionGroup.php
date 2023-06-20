<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryQuestionGroup
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()
            ->title('Question Group')
            ->description('Question Group')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('gid')->default(null),
                Schema::integer('sid')->default(null),
                Schema::integer('groupOrder')->default(null),
                Schema::string('randomizationGroup')->default(null),
                Schema::string('gRelevance')->default(null),
                Schema::create('l10ns')
                    ->additionalProperties(
                        (new SchemaFactoryQuestionGroupL10ns())->make()
                    ),
                ...$properties
            );
    }
}
