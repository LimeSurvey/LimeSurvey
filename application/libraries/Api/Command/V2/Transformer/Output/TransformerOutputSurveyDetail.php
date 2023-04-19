<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

/**
 * TransformerOutputSurveyDetail
 */
class TransformerOutputSurveyDetail extends TransformerOutputActiveRecord
{
    private $transformerSurvey = null;
    private $transformerQuestionGroup = null;
    private $transformerQuestionGroupL10ns = null;
    private $transformerQuestion = null;
    private $transformerQuestionL10ns = null;
    private $transformerQuestionAttribute = null;
    private $transformerAnswer = null;

    /**
     * TransformerOutputSurveyDetail
     */
    public function __construct()
    {
        $this->transformerSurvey = new TransformerOutputSurvey();
        $this->transformerQuestionGroup = new TransformerOutputQuestionGroup();
        $this->transformerQuestionGroupL10ns = new TransformerOutputQuestionGroupL10ns();
        $this->transformerQuestion = new TransformerOutputQuestion();
        $this->transformerQuestionL10ns = new TransformerOutputQuestionL10ns();
        $this->transformerQuestionAttribute = new TransformerOutputQuestionAttribute();
        $this->transformerAnswer = new TransformerOutputAnswer();
    }

    /**
     * Transform
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param Survey $surveyModel
     * @return array
     */
    public function transform($surveyModel)
    {
        $survey =  $this->transformerSurvey->transform($surveyModel);

        $survey['languages'] = $surveyModel->allLanguages;

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = $this->transformerQuestionGroup->transformAll(
            $surveyModel->groups
        );

        // An array of groups indexed by gid for easy look up
        // - helps us to retain sort order when looping over models
        $groupLookup = $this->createCollectionLookup(
            'gid',
            $survey['questionGroups']
        );

        foreach ($surveyModel->groups as $questionGroupModel) {
            // order of groups from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            $group = &$groupLookup[$questionGroupModel->gid];

            $group['l10ns'] = $this->transformerQuestionGroupL10ns->transformAll(
                $questionGroupModel->questiongroupl10ns
            );

            // transformAll() can apply required entity sort so we must retain the sort order going forward
            // - We use a lookup array later to access entities without needing to know their position in the collection
            $group['questions'] = $this->transformerQuestion->transformAll(
                $questionGroupModel->questions
            );
            $questionLookup = $this->createCollectionLookup(
                'qid',
                $group['questions']
            );
            $this->transformQuestions(
                $questionLookup,
                $questionGroupModel->questions
            );
        }

        return $survey;
    }

    /**
     * Transform Questions
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param array $questionLookup
     * @param array $questions
     * @return array
     */
    private function transformQuestions($questionLookup, $questions)
    {
        foreach ($questions as $questionModel) {
            // questions from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            $question = &$questionLookup[$questionModel->qid];

            $question['l10ns'] = $this->transformerQuestionL10ns->transformAll(
                $questionModel->questionl10ns
            );

            $question['attributes'] = $this->transformerQuestionAttribute->transformAll(
                $questionModel->questionattributes
            );

            if ($questionModel->subquestions) {
                $question['subquestions'] = $this->transformerQuestion->transformAll(
                    $questionModel->subquestions
                );

                $subQuestionLookup = $this->createCollectionLookup(
                    'qid',
                    $question['subquestions']
                );
                $this->transformQuestions(
                    $subQuestionLookup,
                    $questionModel->subquestions
                );
            }

            $question['answers'] = $this->transformerAnswer->transformAll(
                $questionModel->answers
            );
        }
    }

    /**
     * Create collection lookup
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param string $key
     * @param array &$entityArray
     * @return array Entity reference
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
