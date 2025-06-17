<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyResponsesPatch
{
    public function make(): Schema
    {
        // Define the response schema for patch operations
        return Schema::create()
            ->title('Survey Responses Patch Result')
            ->description('Result of applying patch operations to survey responses')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('operationsApplied')
                    ->description('Number of operations successfully applied')
                    ->example(1),
                Schema::array('validationErrors')
                    ->description('List of validation errors encountered')
                    ->nullable()
                    ->items(
                        Schema::object()
                            ->properties(
                                Schema::string('entity')->example('response'),
                                Schema::string('op')->example('update'),
                                Schema::string('id')->example('12345'),
                                Schema::array('errors')
                                    ->items(
                                        Schema::object()
                                            ->properties(
                                                Schema::string('field')->example('answers.896595X696X14781.value'),
                                                Schema::string('message')->example('Value is required')
                                            )
                                    )
                            )
                    ),
                Schema::array('exceptionErrors')
                    ->description('List of exceptions encountered')
                    ->nullable()
                    ->items(
                        Schema::object()
                            ->properties(
                                Schema::string('message')->example('Response not found'),
                                Schema::integer('code')->example(404),
                                Schema::object('operation')
                                    ->properties(
                                        Schema::string('entity')->example('response'),
                                        Schema::string('op')->example('update'),
                                        Schema::string('id')->example('12345')
                                    )
                            )
                    )
            );
    }
}