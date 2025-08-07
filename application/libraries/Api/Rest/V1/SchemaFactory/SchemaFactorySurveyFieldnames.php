<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyFieldnames
{
    public function make(): Schema
    {
        $questionItemSchema = Schema::object()
            ->properties(
                Schema::string('fieldname')->example('Q1'),
                Schema::string('type')->example('L'),
                Schema::integer('sid'),
                Schema::integer('gid'),
                Schema::integer('qid'),
                Schema::string('aid')->nullable(),
                Schema::string('title')->example('Q001'),
                Schema::string('question')->example('<p>List radio&nbsp; (L)</p>'),
                Schema::string('group_name')->example('My first question group'),
                Schema::string('mandatory')->example('N'),
                Schema::string('encrypted')->example('N'),
                Schema::string('hasconditions')->example('N'),
                Schema::string('usedinconditions')->example('N'),
                Schema::integer('questionSeq'),
                Schema::integer('groupSeq'),
                Schema::string('relevance')->nullable(),
                Schema::string('grelevance')->nullable(),
                Schema::string('preg')->nullable(),
                Schema::string('other')->nullable(),
                Schema::string('help')->nullable(),
                Schema::string('suffix')->nullable(),
                Schema::integer('sqid')->nullable(),
                Schema::string('subquestion')->nullable(),
                Schema::string('subquestion1')->nullable(),
                Schema::string('subquestion2')->nullable(),
                Schema::integer('scale_id')->nullable(),
                Schema::string('scale')->nullable(),
                Schema::array('answerList')->items(
                    Schema::object()->properties(
                        Schema::string('code'),
                        Schema::string('answer'),
                        Schema::integer('qid')
                    )
                ),
                Schema::string('SQrelevance')->nullable()
            );

        return Schema::create()
            ->title('Survey Fieldnames')
            ->description('Survey questions with their fieldnames and properties')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::object()->additionalProperties(
                    Schema::array()->items($questionItemSchema)
                )
            );
    }
}
