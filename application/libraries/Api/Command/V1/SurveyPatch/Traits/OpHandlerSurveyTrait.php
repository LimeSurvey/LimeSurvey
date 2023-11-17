<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

trait OpHandlerSurveyTrait
{
    use OpHandlerExceptionTrait;

    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     */
    public function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        $surveyId = isset($context['id']) ? (int)$context['id'] : null;
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
     * @param OpInterface $op
     * @param TransformerInterface $transformer
     * @param string $entity
     * @return array
     * @throws OpHandlerException
     */
    public function getTransformedLanguageProps(
        OpInterface $op,
        TransformerInterface $transformer,
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
            $transformedProps = $transformer->transform($properties);
            if ($transformedProps == null) {
                $this->throwNoValuesException($op, $entity);
            }
            $dataSet[$language] = $transformedProps;
        }
        return $dataSet;
    }

    /**
     * returns and removes tempId from dataset
     * @param array $dataSet
     * @return int|mixed
     */
    public function extractTempId(array &$dataSet)
    {
        if (isset($dataSet['tempId'])) {
            $tempId = $dataSet['tempId'];
            unset($dataSet['tempId']);
            return $tempId;
        }
        return 0;
    }
}
