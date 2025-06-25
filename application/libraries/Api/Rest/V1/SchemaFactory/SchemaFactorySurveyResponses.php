<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyResponses
{
    public function make(): Schema
    {
        // Define the answer schema with example
        $answerSchema = Schema::object()
            ->properties(
                Schema::string('key')->example('896595X696X14781'),
                Schema::string('id')->example('14781'),
                Schema::integer('gid')->example(696),
                Schema::string('sid')->example('896595'),
                Schema::string('value')->example('Long text answer'),
                Schema::integer('qid')->example(14781),
                Schema::string('aid')->example(''),
                Schema::integer('sqid')->nullable()->example(null)
            );

        // Define the survey question schema with example
        $surveyQuestionSchema = Schema::object()
            ->properties(
                Schema::integer('gid')->example(696),
                Schema::integer('qid')->example(14781),
                Schema::string('aid')->example(''),
                Schema::integer('sqid')->nullable()->example(null)
            );

        // Define the response schema with example answers
        $responseSchema = Schema::object()
            ->properties(
                Schema::integer('id')->example(1),
                Schema::string('language')->example('en'),
                Schema::string('seed')->example('1600554114'),
                Schema::integer('lastPage')->example(1),
                Schema::string('submitDate')->format(Schema::FORMAT_DATE_TIME)->example('2025-06-12T08:19:23.000Z'),
                Schema::string('startDate')->example('2025-06-12 08:19:01'),
                Schema::string('dateLastAction')->example('2025-06-12 08:19:23'),
                Schema::boolean('completed')->example(true),
                Schema::object('answers')
                    ->additionalProperties($answerSchema)
                    ->example([
                        '896595X696X14781' => [
                            'key' => '896595X696X14781',
                            'id' => '14781',
                            'gid' => 696,
                            'sid' => '896595',
                            'value' => 'Long text answer',
                            'qid' => 14781,
                            'aid' => '',
                            'sqid' => null
                        ],
                        '896595X696X14782SQ001' => [
                            'key' => '896595X696X14782SQ001',
                            'id' => '14782SQ001',
                            'gid' => 696,
                            'sid' => '896595',
                            'value' => 'Y',
                            'qid' => 14782,
                            'aid' => 1234,
                            'sqid' => 14783
                        ]
                    ])
            );

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