<?php

namespace LimeSurvey\Api\Rest\V2\Schema\Question;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

$l10ns = require(__DIR__ . '/QuestionGroupL10ns');

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
        ->properties(
            AllOf::create()->schemas(
                QuestionGroupL10ns
            )
        )
    );
