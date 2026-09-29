<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyResponses
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function make(): Schema
    {
        // Define the survey question schema with example
        $surveyQuestionSchema = Schema::object()
            ->properties(
                Schema::integer('gid')->example(696),
                Schema::integer('qid')->example(14781),
                Schema::string('aid')->example(''),
                Schema::integer('sqid')->nullable()->example(null)
            );

        // Define the response schema with example answers
        $responseSchema = (new SchemaFactorySurveySingleResponse())->make();

        // Define the pagination schema
        $paginationSchema = Schema::object('pagination')
            ->properties(
                Schema::integer('pageSize')->example(15),
                Schema::integer('currentPage')->example(0),
                Schema::integer('totalItems')->example(3),
                Schema::integer('totalPages')->example(1)
            );

        // Define the meta schema
        $metaSchema = Schema::object('_meta')
            ->properties(
                $paginationSchema,
                Schema::array('filters')->items(Schema::object()),
                Schema::array('sort')->items(Schema::object())
            );

        // Define the responses schema with examples
        return Schema::create()
            ->title('Survey Responses')
            ->description('Survey Responses')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('responses')->items($responseSchema),
                Schema::object('surveyQuestions')
                    ->additionalProperties($surveyQuestionSchema)
                    ->example([
                        '896595X696X14781' => [
                            'gid' => 696,
                            'qid' => 14781,
                            'aid' => '',
                            'sqid' => null
                        ],
                        '896595X696X14782SQ001' => [
                            'gid' => 696,
                            'qid' => 14782,
                            'aid' => 1234,
                            'sqid' => 14783
                        ]
                    ]),
                $metaSchema
            );
    }
}
