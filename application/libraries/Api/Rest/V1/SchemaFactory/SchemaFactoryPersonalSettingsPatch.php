<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use LimeSurvey\Api\Rest\SchemaFactoryInterface;

/**
 * Schema factory for personal settings patch
 */
class SchemaFactoryPersonalSettingsPatch implements SchemaFactoryInterface
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
                // Add more properties as needed
            ],
            'additionalProperties' => false
        ];
    }
}
