<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryQuestionGroupL10ns
{
    public function make(): Schema
    {
        return Schema::create('$language')->properties(
            Schema::integer('id')->default(null),
            Schema::integer('gid')->default(null),
            Schema::integer('group_order')->default(null),
            Schema::string('groupName')->default(null),
            Schema::string('description')->default(null),
            Schema::string('language')->default(null)
        );
    }
}
