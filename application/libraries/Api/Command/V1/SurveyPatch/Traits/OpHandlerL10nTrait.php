<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\Api\Transformer\TransformerInterface;

trait OpHandlerL10nTrait
{
    use OpHandlerExceptionTrait;

    /**
     * Transforms language related props to array
     * This function is shared by QuestionL10n and QuestionGroupL10n operations,
     * so the actual transformer needs to
     * @param OpInterface $op
     * @param array $props
     * @param string $entity
     * @return array
     * @throws OpHandlerException
     */
    private function transformAllLanguageProps(
        OpInterface $op,
        array $props,
        string $entity,
        TransformerInterface $transformer
    ): array {
        $dataSet = [];
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
            if (empty($properties)) {
                throw new OpHandlerException(
                    sprintf(
                        'no props provided for %s with id "%s"',
                        $entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            $this->throwTransformerValidationErrors(
                $transformer->validate(
                    $properties
                ),
                $op
            );

            $transformedProps = $transformer->transform($properties);
            $dataSet[$language] = $transformedProps;
        }
        return $dataSet;
    }
}
