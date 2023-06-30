<?php

namespace LimeSurvey\Models\Services\QuestionEditor;

use Question;
use Survey;
use Condition;
use LSYii_Application;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorL10n
};

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyQuestion
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * Question Editor Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionEditorQuestion
{
    private Question $modelQuestion;
    private Survey $modelSurvey;
    private Condition $modelCondition;
    private QuestionEditorL10n $questionEditorL10n;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyQuestion $proxyQuestion;
    private LSYii_Application $yiiApp;

    public function __construct(
        Question $modelQuestion,
        Survey $modelSurvey,
        Condition $modelCondition,
        QuestionEditorL10n $questionEditorL10n,
        ProxySettingsUser $proxySettingsUser,
        ProxyQuestion $proxyQuestion,
        LSYii_Application $yiiApp
    ) {
        $this->modelQuestion = $modelQuestion;
        $this->modelSurvey = $modelSurvey;
        $this->modelCondition = $modelCondition;
        $this->questionEditorL10n = $questionEditorL10n;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->proxyQuestion = $proxyQuestion;
        $this->yiiApp = $yiiApp;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array{
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
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @return Question
     */
    public function save($input)
    {
        $input  = $input ?? [];

        $data = [];
        $data['question']         = $input['question'] ?? [];
        $data['question']['sid']  = $data['question']['sid'] ?? ($input['sid'] ?? null);
        $data['question']['qid']  = $data['question']['qid'] ?? null;

        $question = $this->modelQuestion
            ->findByPk((int) $data['question']['qid']);

        if (empty($data['question']['qid'])) {
            $data['question']['qid'] = null;
            $question = $this->storeNewQuestionData(
                $data['question']
            );
        } else {
            // Store changes to the actual question data,
            // by either storing it, or updating an old one
            $question = $this->updateQuestionData(
                $question,
                $data['question']
            );
        }

        $this->saveDefaults($data);

        return $question;
    }

    /**
     * Method to store and filter data for a new question
     *
     * todo: move to model or service class
     *
     * @param array $data
     * @param boolean $subQuestion
     * @return Question
     * @throws PersistErrorException
     */
    private function storeNewQuestionData($data = null, $subQuestion = false)
    {
        $surveyId = $data['sid'];
        $survey = $this->modelSurvey
            ->findByPk($surveyId);
        $questionGroupId = (int) $data['gid'];
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

        $question = new Question();
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

        // Init empty L10n records
        foreach ($survey->allLanguages as $language) {
            $this->questionEditorL10n->save(
                $question->qid,
                array(
                    [
                        'language' => $language,
                        'question' => '',
                        'help' => '',
                        'script' => ''
                    ]
                )
            );
        }

        return $question;
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
}
