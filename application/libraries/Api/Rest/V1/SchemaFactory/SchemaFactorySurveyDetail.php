<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveyDetail
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function make(): Schema
    {
        $questionAttributeSchema = (new SchemaFactoryQuestionAttribute())->make();
        $questionAttributesSchema = Schema::object('attributes')->additionalProperties(
            $questionAttributeSchema
        );

        $answerSchema = (new SchemaFactoryAnswer())->make();
        $answersSchema = Schema::array('answers')->items(
            AllOf::create()->schemas(
                $answerSchema
            )
        );

        $questionSchema = (new SchemaFactoryQuestion())->make(
            $answersSchema,
            $questionAttributesSchema
        );
        $questionsSchema = Schema::object('questions')->properties(
            AllOf::create('0')->schemas(
                $questionSchema
            )
        );

        $questionGroupSchema = (new SchemaFactoryQuestionGroup())->make(
            $questionsSchema
        );


        $surveySchema = AllOf::create('survey')->schemas(
            (new SchemaFactorySurvey())->make(
                Schema::array('languages')->items(
                    AllOf::create()->schemas(
                        Schema::string()
                    )
                ),
                Schema::object('questionGroups')->properties(
                    AllOf::create('0')->schemas(
                        $questionGroupSchema
                    )
                ),
                Schema::string('created_at')
                    ->format(Schema::FORMAT_DATE_TIME)
            )
        );

        return Schema::create()
            ->title('Survey Detail')
            ->description('Survey Detail')
            ->type(Schema::TYPE_OBJECT)
            ->properties($surveySchema);
    }
}
