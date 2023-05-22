<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactorySurveyDetail
{
    public function create(): Schema
    {
        $schemaSurveyLanguageSettings = (new SchemaFactorySurveyLanguageSettings)->create();
        $schemaSurveyDefaultLanguage = Schema::object('defaultlanguage')
            ->properties(...$schemaSurveyLanguageSettings->properties);

        $questionAttributeSchema = (new SchemaFactoryQuestionAttribute)->create();
        $questionAttributesSchema = Schema::object('attributes')->additionalProperties(
            $questionAttributeSchema
        );

        $answerSchema = (new SchemaFactoryAnswer)->create();
        $answersSchema = Schema::array('answers')->items(
            AllOf::create()->schemas(
                $answerSchema
            )
        );

        $questionSchema = (new SchemaFactoryQuestion)->create();
        $questionSchema = $questionSchema->properties(
            ...array_merge(
                $questionSchema->properties,
                [$questionAttributesSchema, $answersSchema]
            )
        );
        $questionsSchema = Schema::array('questions')->items(
            AllOf::create()->schemas(
                $questionSchema
            )
        );

        $questionGroupSchema = (new SchemaFactoryQuestionGroup)->create();
        $questionGroupSchema = $questionGroupSchema->properties(
            ...array_merge(
            $questionGroupSchema->properties,
            [$questionsSchema]
            )
        );

        $props = [
            AllOf::create()->schemas(
                (new SchemaFactorySurvey)->create(),
                Schema::array('languages')->items(
                    AllOf::create()->schemas(
                        Schema::string()
                    )
                ),
                Schema::array('questionGroups')->items(
                    AllOf::create()->schemas(
                        $questionGroupSchema
                    )
                ),
                Schema::string('created_at')->format(Schema::FORMAT_DATE_TIME)
            )
        ];

        return Schema::create()
            ->title('Survey Detail')
            ->description('Survey Detail')
            ->type(Schema::TYPE_OBJECT)
            ->properties(...$props);
    }
}
