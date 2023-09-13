<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

trait OpHandlerSurveyTrait
{
    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     */
    public function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        $surveyId = isset($context['id']) ? (int) $context['id'] : null;
        if ($surveyId === null) {
            throw new OpHandlerException(
                printf(
                    'Missing survey id in context for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $surveyId;
    }

    /**
     * transforms language related props to array
     */
    public function getTransformedLanguageProps(
        OpInterface $op,
        string $entity
    ): array {
        $dataSet = [];
        $props = $op->getProps();
        foreach ($props as $language => $properties) {
            if (is_numeric($language)) {
                throw new OpHandlerException(
                    sprintf(
                        'no indexes for language provided within props for %s with id "%s"',
                        $entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            $transformedProps = $this->transformer->transform($properties);
            if ($transformedProps == null) {
                throw new OpHandlerException(
                    sprintf(
                        'no transformable props provided for %s with id "%s"',
                        $entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            $dataSet[$language] = $transformedProps;
        }
        return $dataSet;
    }
}
