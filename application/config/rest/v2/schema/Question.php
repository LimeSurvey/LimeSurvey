<?php

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

return Schema::create()
    ->title('Question')
    ->description('Question')
    ->type(Schema::TYPE_OBJECT)
    ->properties(
        Schema::integer('qid')->default(null),
        Schema::integer('parent_qid')->default(0),
        Schema::integer('sid')->default(null),
        Schema::string('type')->default(null),
        Schema::string('title')->default(null),
        Schema::string('preg')->default(null),
        Schema::boolean('other')->default(null),
        Schema::boolean('mandatory')->default(null),
        Schema::boolean('encrypted')->default(null),
        Schema::integer('question_order')->default(0),
        Schema::integer('scale_id')->default(null),
        Schema::boolean('same_default')->default(null),
        Schema::boolean('mandatory')->default(null),
        Schema::string('question_theme_name')->default(null),
        Schema::string('modulename')->default(null),
        Schema::integer('gid')->default(0),
        Schema::string('relevance')->default(null),
        Schema::string('same_script')->default(null),
        Schema::boolean('mandatory')->default(null)
    );
