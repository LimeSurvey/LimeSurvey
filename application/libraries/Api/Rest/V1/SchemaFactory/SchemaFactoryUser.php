<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryUser
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('User')
            ->description('User')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('uid')->default(null),
                Schema::string('name')->default(null),
                Schema::string('fullName')->default(null),
                Schema::integer('parentId')->default(null),
                Schema::string('lang')->default(null),
                Schema::string('email')->default(null),
                Schema::string('htmlEditorMode')->default(null),
                Schema::string('templateEditorMode')->default(null),
                Schema::string('questionSelectorMode')->default(null),
                Schema::integer('dateFormat')->default(null),
                Schema::boolean('showQuestionCodes')->default(false),
                Schema::string('lastLogin')->default(null),
                Schema::string('created')->default(null),
                Schema::string('modified')->default(null),
                Schema::integer('userStatus')->default(null),
            );
    }
}
