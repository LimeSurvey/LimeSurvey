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

        $survey['questionGroups'] = (
            new TransformerOutputQuestionGroup()
        )->transformAll(
            $surveyModel->groups
        );

        $transformerQuestion = new TransformerOutputQuestion();
        $transformerQuestionAttribute = new TransformerOutputQuestionAttribute();
        foreach ($surveyModel->groups as $groupKey => $questionGroupModel) {
            $survey['questionGroups'][$groupKey]['questions'] = $transformerQuestion->transformAll(
                $questionGroupModel->questions
            );

            foreach ($questionGroupModel->questions as $questionKey => $questionModel) {
                $question = &$survey['questionGroups'][$groupKey]['questions'][$questionKey];
                $question['attributes'] = $transformerQuestionAttribute->transformAll(
                    // questionattributes is returned as an associative array keyed on 'attribute'
                    // - so we need to call array_values to get the array of QuestionAttribute models
                    array_values($questionModel->questionattributes)
                );
            }
        }

        return $survey;
    }
}
