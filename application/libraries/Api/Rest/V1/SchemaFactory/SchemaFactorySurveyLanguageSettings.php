<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyLanguageSettings
{
    public function create(): Schema
    {
        return Schema::create()
            ->title('Survey Language Settings')
            ->description('Survey Language Settings')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('sid')->default(null),
                Schema::string('language')->default(null),
                Schema::string('title')->default(null),
                Schema::string('description')->default(null),
                Schema::string('welcometext')->default(null),
                Schema::string('endtext')->default(null),
                Schema::string('policy_notice')->default(null),
                Schema::string('policy_error')->default(null),
                Schema::string('policy_notice_label')->default(null),
                Schema::string('url')->default(null),
                Schema::string('urldescription')->default(null),
                Schema::integer('dateformat')->default(null),
                Schema::integer('numberformat')->default(null)
            );
    }
}
