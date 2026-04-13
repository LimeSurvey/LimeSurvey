<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyLanguageSettings
{
    public function make(): Schema
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
                Schema::string('welcomeText')->default(null),
                Schema::string('endText')->default(null),
                Schema::string('policyNotice')->default(null),
                Schema::string('policyError')->default(null),
                Schema::string('policyNoticeLabel')->default(null),
                Schema::string('url')->default(null),
                Schema::string('urlDescription')->default(null),
                Schema::integer('dateFormat')->default(null),
                Schema::integer('numberFormat')->default(null)
            );
    }
}
