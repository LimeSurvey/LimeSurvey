<?php

namespace LimeSurvey\Api\Command\V2;

use Survey;
use QuestionGroup;
use Question;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

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
                //'groups.questions.subquestions'
            )->findByPk($surveyId);

        if (!$surveyModel) {
            return null;
        }

        $survey = $surveyModel->attributes;
        $survey['languages'] = $surveyModel->allLanguages;

       // $this->populateQuestionGroups($survey);

        return $this->responseSuccess([$survey, $surveyModel->groups]);
    }

    protected function populateQuestionGroups(&$survey)
    {
        $questionGroups = $this->collectionToArray(QuestionGroup::model()
            ->findAllByAttributes(
                array('sid' => $survey['sid'])
            ));

        $questions = $this->collectionToArray(Question::model()
            ->findAllByAttributes(
                array('sid' => $survey['sid'])
            ));

        $survey['questionGroups'] = $questionGroups;
        if (!empty($survey['questionGroups'])) {
            foreach ($survey['questionGroups'] as $key => $questionGroup) {
                $survey['questionGroups'][$key]['questions'] = $this->getQuestions(
                    $questionGroup['gid'],
                    $questions
                );
            }
        }
    }

    protected function getQuestions($questionGroupId, $questions)
    {
        return array_filter(
            $questions,
            function ($question) use ($questionGroupId) {
                if (
                    $question['gid'] == $questionGroupId
                    && $question['parent_qid'] > 0
                ) {
                    return $question;
                }
                return false;
            }
        );
    }

    protected function collectionToArray($collection)
    {
        $data = [];
        if ($collection) {
            foreach ($collection as $entity) {
                if ($entity) {
                    $data[] = $entity->attributes;
                }
            }
        }
        return $data;
    }
}
