<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySurvey
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $schemaSurveyLanguageSettings = (new SchemaFactorySurveyLanguageSettings())->make();
        $schemaSurveyDefaultLanguage = Schema::object('defaultlanguage')
            ->properties(...$schemaSurveyLanguageSettings->properties);

        return Schema::create()
            ->title('Survey')
            ->description('Survey')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('sid')->default(null),
                Schema::integer('gsid')->default(null),
                Schema::boolean('active')->default(false),
                Schema::boolean('expires')->default(null),
                Schema::boolean('startDate')->default(null),
                Schema::boolean('anonymized')->default(null),
                Schema::boolean('saveTimings')->default(null),
                Schema::boolean('datestamp')->default(null),
                Schema::boolean('useCookie')->default(null),
                Schema::boolean('allowRegister')->default(null),
                Schema::boolean('allowSave')->default(null),
                Schema::integer('autoNumberStart')->default(0),
                Schema::boolean('autoRedirect')->default(null),
                Schema::boolean('allowPrev')->default(null),
                Schema::boolean('printAnswers')->default(null),
                Schema::boolean('ipAddr')->default(null),
                Schema::boolean('ipAnonymize')->default(null),
                Schema::boolean('refUrl')->default(null),
                Schema::string('dateCreated')->default(null)->format(Schema::FORMAT_DATE_TIME),
                Schema::boolean('publicStatistics')->default(null),
                Schema::boolean('publicGraphs')->default(null),
                Schema::boolean('listPublic')->default(null),
                Schema::boolean('sendConfirmation')->default(null),
                Schema::boolean('tokenAnswersPersistence')->default(null),
                Schema::boolean('assessments')->default(null),
                Schema::boolean('useCaptcha')->default(null),
                Schema::boolean('useTokens')->default(null),
                Schema::string('bounceEmail')->default(null),
                Schema::string('attributeDescriptions')->default(null),
                Schema::boolean('emailResponseTo')->default(null),
                Schema::string('emailNotificationTo')->default(null),
                Schema::integer('tokenLength')->default(null)->example(15),
                Schema::boolean('showXQuestions')->default(null),
                Schema::boolean('showGroupInfo')->default(null),
                Schema::boolean('showNoAnswer')->default(null),
                Schema::boolean('showQNumCode')->default(null),
                Schema::integer('bounceTime')->default(null),
                Schema::boolean('bounceProcessing')->default(null),
                Schema::string('bounceAccountType')->default(null),
                Schema::string('bounceAccountHost')->default(null),
                Schema::string('bounceAccountPass')->default(null),
                Schema::string('bounceAccountEncryption')->default(null),
                Schema::string('bounceAccountUser')->default(null),
                Schema::boolean('showWelcome')->default(null),
                Schema::boolean('showProgress')->default(null),
                Schema::integer('questionIndex')->default(null),
                Schema::integer('navigationDelay')->default(null),
                Schema::boolean('noKeyboard')->default(null),
                Schema::boolean('allowedItAfterCompletion')->default(null),
                Schema::integer('googleAnalyticsStyle')->default(null),
                Schema::string('googleAnalyticsApiKey')->default(null),
                Schema::integer('showSurveyPolicyNotice')->default(null),
                $schemaSurveyDefaultLanguage,
                ...$properties
            );
    }
}
