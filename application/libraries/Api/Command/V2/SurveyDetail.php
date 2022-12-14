<?php

namespace LimeSurvey\Api\Command\V2;

use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;
use LimeSurvey\Api\Command\V2\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\V2\Transformer\Output\TransformerOutputQuestionGroup;
use LimeSurvey\Api\Command\V2\Transformer\Output\TransformerOutputQuestion;
use LimeSurvey\Api\Command\V2\Transformer\Output\TransformerOutputQuestionAttribute;

class SurveyDetail implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

    /**
     * Run survey detail command
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $surveyId = (string) $request->getData('surveyId');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $surveyModel = Survey::model()
            ->with(
                'groups',
                'groups.questiongroupl10ns',
                'groups.questions',
                'groups.questions.questionl10ns',
                'groups.questions.answers',
                'groups.questions.questionattributes'
                //'groups.questions.subquestions' // Integrity constraint violation: 1052 Column 'parent_qid' in where clause is ambiguous
            )->findByPk($surveyId);

        if (!$surveyModel) {
            return null;
        }

        $survey = $this->transform($surveyModel);

        return $this->responseSuccess(['survey' => $survey]);
    }

    private function transform($surveyModel)
    {
        $survey = (
            new TransformerOutputSurveyDetail()
        )->transform($surveyModel);

        $survey['languages'] = $surveyModel->allLanguages;

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = (
            new TransformerOutputQuestionGroup()
        )->transformAll(
            $surveyModel->groups
        );

        // An array of groups indexed by gid for easy look up
        // - helps use to retain sort order when looping over models
        $groupLookup = $this->createCollectionLookup('gid', $survey['questionGroups']);

        $transformerQuestion = new TransformerOutputQuestion();
        $transformerQuestionAttribute = new TransformerOutputQuestionAttribute();
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
                    // questionattributes is returned as an associative array keyed on 'attribute'
                    // - so we need to call array_values to get the array of QuestionAttribute models
                    array_values($questionModel->questionattributes)
                );
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
