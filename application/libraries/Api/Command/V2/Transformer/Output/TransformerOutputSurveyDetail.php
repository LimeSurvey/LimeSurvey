<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecordAbstract;

class TransformerOutputSurveyDetail extends TransformerOutputActiveRecordAbstract
{
    public function transform($surveyModel)
    {
        $survey = (new TransformerOutputSurvey())->transform($surveyModel);

        $survey['languages'] = $surveyModel->allLanguages;

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = (new TransformerOutputQuestionGroup())->transformAll(
            $surveyModel->groups
        );

        // An array of groups indexed by gid for easy look up
        // - helps use to retain sort order when looping over models
        $groupLookup = $this->createCollectionLookup('gid', $survey['questionGroups']);

        $transformerQuestion = new TransformerOutputQuestion();
        $transformerQuestionAttribute = new TransformerOutputQuestionAttribute();
        $transformerAnswer = new TransformerOutputAnswer();

        foreach ($surveyModel->groups as $questionGroupModel) {
            // order of groups from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            $group = &$groupLookup[$questionGroupModel->gid];

            // transformAll() can apply required entity sort so we must retain the sort order going forward
            // - We use a lookup array later to access entities without needing to know their position in the collection
            $group['questions'] = $transformerQuestion->transformAll(
                $questionGroupModel->questions
            );

            $questionLookup = $this->createCollectionLookup('qid', $group['questions']);

            foreach ($questionGroupModel->questions as $questionModel) {
                // questions from the model relation may be different than from the transformed data
                // - so we use the lookup to get a reference to the required entity without needing to
                // - know its position in the output array
                $question = &$questionLookup[$questionModel->qid];

                $question['attributes'] = $transformerQuestionAttribute->transformAll(
                    $questionModel->questionattributes
                );

                $question['answers'] = $transformerAnswer->transformAll(
                    $questionModel->answers
                );

                // $answerLookup = $this->createCollectionLookup('aid', $group['answers']);
            }
        }

        return $survey;
    }

    /**
     * Creation collection lookup
     *
     * Returns an array of entity references keyed on the specified key.
     *
     * @param string $key
     * @param array $entityArray
     * @return &array Entity reference
     */
    private function createCollectionLookup($key, &$entityArray)
    {
        $output = [];
        foreach ($entityArray as &$entity) {
            if (is_array($entity) && isset($entity[$key])) {
                $output[$entity[$key]] = &$entity;
            }
        }
        return $output;
    }
}
