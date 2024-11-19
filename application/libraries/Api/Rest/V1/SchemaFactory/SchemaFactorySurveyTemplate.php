<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveyTemplate
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function make(): Schema
    {
        $questionAttributeSchemas = (new SchemaFactoryQuestionAttributes())->make();

        $answerSchema = (new SchemaFactoryAnswer())->make();
        $answersSchema = Schema::array('answers')->items(
            AllOf::create()->schemas(
                $answerSchema
            )
        );

        $questionSchema = (new SchemaFactoryQuestion())->make(
            $answersSchema,
            Schema::object('attributes')->properties(
                $questionAttributeSchemas
            )
        );
        $questionsSchema = Schema::array('questions')->items(
            $questionSchema
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
                Schema::array('questionGroups')->items(
                    $questionGroupSchema
                ),
                Schema::string('created_at')
                    ->format(Schema::FORMAT_DATE_TIME)
            )
        );

        return Schema::create()
            ->title('Survey Template')
            ->description('Survey Template')
            ->type(Schema::TYPE_OBJECT)
            ->properties($surveySchema);
    }
}
