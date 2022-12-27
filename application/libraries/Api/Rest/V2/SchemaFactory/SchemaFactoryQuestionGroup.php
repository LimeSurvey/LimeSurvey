<?php

namespace LimeSurvey\Api\Rest\V2\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactoryQuestionGroup
{
    public function create(): Schema
    {
        return Schema::create()
            ->title('Question Group')
            ->description('Question Group')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('gid')->default(null),
                Schema::integer('sid')->default(null),
                Schema::integer('group_order')->default(null),
                Schema::string('randomization_group')->default(null),
                Schema::string('grelevance')->default(null),
                Schema::create('l10ns')
                    ->additionalProperties(
                        (new SchemaFactoryQuestionGroupL10ns())->create()
                    )
            );
    }
}
