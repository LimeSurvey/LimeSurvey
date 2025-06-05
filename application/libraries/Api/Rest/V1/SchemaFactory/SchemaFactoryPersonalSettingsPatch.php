<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

/**
 * Schema factory for personal settings patch
 */
class SchemaFactoryPersonalSettingsPatch
{
    /**
     * Create the schema
     *
     * @return array
     */
    public function make(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'answeroptionprefix' => [
                    'type' => 'string',
                    'description' => 'Answer option prefix'
                ],
                'subquestionprefix' => [
                    'type' => 'string',
                    'description' => 'Subequestion prefix'
                ],
                'showQuestionCodes' => [
                    'type' => 'boolean',
                    'description' => 'Show question codes preference'
                ]
            ],
            'additionalProperties' => false
        ];
    }
}
