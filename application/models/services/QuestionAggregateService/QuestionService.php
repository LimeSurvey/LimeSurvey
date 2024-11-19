<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use QuestionAttribute;
use Survey;
use Condition;
use LSYii_Application;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\{
    Proxy\ProxySettingsUser,
    Proxy\ProxyQuestion,
    Exception\PersistErrorException,
    Exception\NotFoundException,
    Exception\PermissionDeniedException
};

/**
 * Question Aggregate Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionService
{
    private Question $modelQuestion;
    private Survey $modelSurvey;
    private Condition $modelCondition;
    private L10nService $l10nService;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyQuestion $proxyQuestion;
    private LSYii_Application $yiiApp;

    public function __construct(
        Question $modelQuestion,
        Survey $modelSurvey,
        Condition $modelCondition,
        L10nService $l10nService,
        ProxySettingsUser $proxySettingsUser,
        ProxyQuestion $proxyQuestion,
        LSYii_Application $yiiApp
    ) {
        $this->modelQuestion = $modelQuestion;
        $this->modelSurvey = $modelSurvey;
        $this->modelCondition = $modelCondition;
        $this->l10nService = $l10nService;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->proxyQuestion = $proxyQuestion;
        $this->yiiApp = $yiiApp;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array {
     *  ?sid: int,
     *  ?same_default: int,
     *  ?question: array{
     *      ?qid: int,
     *      ?sid: int,
     *      ?gid: int,
     *      ?type: string,
     *      ?other: string,
     *      ?mandatory: string,
     *      ?relevance: int,
     *      ?group_name: string,
     *      ?modulename: string,
     *      ?encrypted: string,
     *      ?subqestions: array,
     *      ?save_as_default: string,
     *      ?clear_default: string,
     *      ...<array-key, mixed>
     *  }
     * } $input
     * @return Question
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function save($input)
    {
        $input = $input ?? [];

        $data = [];
        $data['question'] = $input['question'] ?? [];
        $data['question']['sid'] = $data['question']['sid'] ?? ($input['sid'] ?? null);
        $data['question']['qid'] = $data['question']['qid'] ?? null;

        // Store changes to the actual question data,
        // by either creating it, or updating an existing one
        if (empty($data['question']['qid'])) {
            $data['question']['qid'] = null;
            $question = $this->storeNewQuestionData(
                $data['question']
            );
        } else {
            $question = $this->getQuestionBySidAndQid(
                $data['question']['sid'],
                $data['question']['qid']
            );

            if (!$question) {
                throw new NotFoundException(
                    sprintf(
                        'Could not find question with id "%s" in survey ID "%s"',
                        $data['question']['qid'],
                        $data['question']['sid']
                    )
                );
            }

            $question = $this->updateQuestionData(
                $question,
                $data['question']
            );
        }

        $this->saveDefaults($input);

        return $question;
    }

    /**
     * Method to store and filter data for a new question
     *
     * @param array $data
     * @param boolean $subQuestion
     * @return Question
     * @throws PersistErrorException
     */
    private function storeNewQuestionData($data = null, $subQuestion = false)
    {
        $data = $data ?? [];
        $surveyId = $data['sid'] ?? 0;
        $survey = $this->modelSurvey
            ->findByPk($surveyId);
        $questionGroupId = $data['gid'] ?? 0;
        $type = $this->proxySettingsUser->getUserSettingValue(
            'preselectquestiontype',
            null,
            null,
            null,
            $this->yiiApp
                ->getConfig('preselectquestiontype')
        );

        if (isset($data['same_default'])) {
            if ($data['same_default'] == 1) {
                $data['same_default'] = 0;
            } else {
                $data['same_default'] = 1;
            }
        }
        if (!isset($data['same_script'])) {
            $data['same_script'] = 0;
        }

        $data = array_merge(
            [
                'sid'        => $surveyId,
                'gid'        => $questionGroupId,
                'type'       => $type,
                'other'      => 'N',
                'mandatory'  => 'N',
                'relevance'  => 1,
                'group_name' => '',
                'modulename' => '',
                'encrypted'  => 'N'
            ],
            $data
        );
        unset($data['qid']);

        if ($subQuestion) {
            foreach ($survey->allLanguages as $language) {
                unset($data[$language]);
            }
        } else {
            $data['question_order'] = $this->proxyQuestion
                ->getMaxQuestionOrder($questionGroupId);
        }

        $question = $this->saveQuestionData($data, $questionGroupId);

        $this->initL10nService($survey, $question->qid);

        return $question;
    }

    /**
     * Save question data
     *
     * @param array $data
     * @param int $questionGroupId
     * @return Question
     */
    private function saveQuestionData($data, $questionGroupId)
    {
        // We use the container to create a model instance
        // allowing us to mock the model instance via
        // container configuration in unit tests
        $question = DI::getContainer()
            ->make(Question::class);
        $question->setAttributes(
            $data,
            false
        );

        // set the question_order the highest existing number +1,
        // if no question exists for the group
        // set the question_order to 1
        $highestOrderNumber = $this->proxyQuestion
            ->getHighestQuestionOrderNumberInGroup(
                $questionGroupId
            );
        if ($highestOrderNumber === null) {
            //this means there is no question inside this group ...
            $question->question_order = Question::START_SORTING_VALUE;
        } else {
            $question->question_order = $highestOrderNumber + 1;
        }

        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Could not save question')
            );
        }

        $question->refresh();

        return $question;
    }

    /**
     * Init L10nService for a question
     *
     * @param Survey $survey
     * @param int $questionId
     * @return void
     */
    private function initL10nService($survey, $questionId)
    {
        foreach ($survey->allLanguages as $language) {
            $this->l10nService->save(
                $questionId,
                array(
                    [
                        'language' => $language,
                        'question' => '',
                        'help'     => '',
                        'script'   => ''
                    ]
                )
            );
        }
    }

    /**
     * Method to store and filter data for editing a question
     *
     * @param Question $question
     * @param array $data
     * @return Question
     * @throws PersistErrorException
     */
    private function updateQuestionData(Question $question, $data)
    {
        // @todo something wrong in frontend ... (?what is wrong?)
        if (isset($data['same_default'])) {
            if ($data['same_default'] == 1) {
                $data['same_default'] = 0;
            } else {
                $data['same_default'] = 1;
            }
        }

        if (!isset($data['same_script'])) {
            $data['same_script'] = 0;
        }

        $originalRelevance = $question->relevance;

        $question->setAttributes($data, false);

        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Update failed, could not save.')
            );
        }

        // If relevance equation was manually edited,
        // existing conditions must be cleared
        if (
            $question->relevance != $originalRelevance
            && !empty($question->conditions)
        ) {
            $this->modelCondition->deleteAllByAttributes(
                ['qid' => $question->qid]
            );
        }

        return $question;
    }

    /**
     * Save defaults
     */
    private function saveDefaults($data)
    {
        // Save advanced attributes default values for given question type
        if (
            array_key_exists(
                'save_as_default',
                $data['question']
            )
            && $data['question']['save_as_default'] == 'Y'
        ) {
            $this->proxySettingsUser->setUserSetting(
                'question_default_values_'
                . $data['question']['type'],
                ls_json_encode(
                    $data['advancedSettings']
                )
            );
        } elseif (
            array_key_exists(
                'clear_default',
                $data['question']
            )
            && $data['question']['clear_default'] == 'Y'
        ) {
            $this->proxySettingsUser->deleteUserSetting(
                'question_default_values_'
                . $data['question']['type']
            );
        }
    }

    /**
     * Returns a question if it exists within the survey.
     * @param int $sid
     * @param int $qid
     * @return Question
     * @throws NotFoundException
     */
    public function getQuestionBySidAndQid(int $sid, int $qid)
    {
        $question = $this->modelQuestion
            ->findByAttributes([
                'qid' => $qid,
                'sid' => $sid
            ]);
        if (!$question) {
            throw new NotFoundException(
                'Question not found'
            );
        }
        return $question;
    }

    /**
     * Returns all(!) question attributes to a question.
     * The default scope on QuestionAttribute which is reset here
     * caused missing data.
     * We need to use this function in TransformerOutputSurveyDetail instead of
     * accessing the attributes with "$questionModel->questionattributes"
     * @param int $questionId
     * @return QuestionAttribute[]
     */
    public function getQuestionAttributes(int $questionId)
    {
        // We use the container to create a model instance
        // allowing us to mock the model instance via
        // container configuration in unit tests
        $model = DI::getContainer()
            ->make(QuestionAttribute::class);
        $model->resetScope();
        return $model->findAllByAttributes(['qid' => $questionId]);
    }
}
