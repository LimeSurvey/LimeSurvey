<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerExceptionTrait;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

trait TransformerInputLanguageTrait
{
    use OpHandlerExceptionTrait;

    /**
     * Transforms language related props to array
     * This function is shared by QuestionL10n and QuestionGroupL10n operations,
     * so the actual transformer needs to
     * @param OpInterface $op
     * @param array $props
     * @param TransformerInterface $transformer
     * @param string $entity
     * @return array
     * @throws OpHandlerException
     */
    public function transformAllLanguageProps(
        OpInterface $op,
        array $props,
        TransformerInterface $transformer,
        string $entity
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
            $transformedProps = $transformer->transform($properties);
            if ($transformedProps == null) {
                $this->throwNoValuesException($op, $entity);
            }
            $dataSet[$language] = $transformedProps;
        }
        return $dataSet;
    }
}
