<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use QuestionL10n;
use Survey;
use Condition;
use Answer;
use AnswerL10n;
use LSYii_Application;

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorAttributes;

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyExpressionManager,
    ProxyQuestion
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException,
    BadRequestException
};

/**
 * Question Editor Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionEditor
{
    private Permission $modelPermission;
    private Question $modelQuestion;
    private QuestionL10n $modelQuestionL10n;
    private QuestionEditorAttributes $questionEditorAttributes;
    private Survey $modelSurvey;
    private Condition $modelCondition;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyQuestion $proxyQuestion;
    private ProxyExpressionManager $proxyExpressionManager;
    private LSYii_Application $yiiApp;

    public function __construct(
        Permission $modelPermission,
        Question $modelQuestion,
        QuestionL10n $modelQuestionL10n,
        QuestionEditorAttributes $questionEditorAttributes,
        Survey $modelSurvey,
        Condition $modelCondition,
        ProxySettingsUser $proxySettingsUser,
        ProxyQuestion $proxyQuestion,
        ProxyExpressionManager $proxyExpressionManager,
        LSYii_Application $yiiApp
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionL10n = $modelQuestionL10n;
        $this->modelSurvey = $modelSurvey;
        $this->modelCondition = $modelCondition;
        $this->questionEditorAttributes = $questionEditorAttributes;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->proxyQuestion = $proxyQuestion;
        $this->proxyExpressionManager = $proxyExpressionManager;
        $this->yiiApp = $yiiApp;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param <array-key, mixed> $input
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @return array
     */
    public function save($input)
    {
        $surveyId = (int) ($input['sid'] ?? 0);

        $data = [];
        $data['question']         = $input['question'] ?? [];
        // TODO: It's l10n, not i10n.
        $data['questionI10N']     = $input['questionI10N'] ?? [];
        $data['advancedSettings'] = $input['advancedSettings'] ?? [];
        $data['question']['sid']  = $surveyId;

        $question = $this->modelQuestion
            ->findByPk((int) $data['question']['qid']);

        $surveyId = $question ? $question->sid : $surveyId;

        // Different permission check when sid vs qid is given.
        // This double permission check is needed if user manipulates the post data.
        if (
            !$this->modelPermission->hasSurveyPermission(
            $surveyId,
            'surveycontent',
            'update'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }

        // Rollback at failure.
        $transaction = $this->yiiApp->db->beginTransaction();
        try {
            if ($data['question']['qid'] == 0) {
                $data['question']['qid'] = null;
                $question = $this->storeNewQuestionData(
                    $data['question']
                );
            } else {
                // Store changes to the actual question data, by either storing it, or updating an old one
                $question = $this->updateQuestionData(
                    $question,
                    $data['question']
                );
            }

            $this->applyL10n(
                $question,
                $data['questionI10N']
            );

            $this->questionEditorAttributes
                ->updateAdvanced(
                    $question,
                    $data['advancedSettings']
                );

            $this->questionEditorAttributes
                ->updateGeneral(
                    $question,
                    $data['question']
                );

            // save advanced attributes default values for given question type
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

            // Clean answer options before save.
            // NB: Still inside a database transaction.
            $question->deleteAllAnswers();
            // If question type has answeroptions, save them.
            if ($question->questionType->answerscales > 0) {
                $this->storeAnswerOptions(
                    $question,
                    $input['answeroptions'] ?? []
                );
            }

            if ($question->survey->active == 'N') {
                // Clean subQuestions before save.
                $question->deleteAllSubquestions();
                // If question type has subQuestions, save them.
                if ($question->questionType->subQuestions > 0) {
                    $this->storeSubquestions(
                        $question,
                        $input['subQuestions'] ?? []
                    );
                }
            } else {
                if ($question->questionType->subQuestions > 0) {
                    $this->updateSubquestions(
                        $question,
                        $input['subQuestions'] ?? []
                    );
                }
            }
            $transaction->commit();

            // All done, redirect to edit form.
            $question->refresh();
            $this->proxyExpressionManager->setDirtyFlag();
        } catch (\Exception $ex) {
            $transaction->rollback();

            throw new PersistErrorException(
                sprintf(
                    'Failed saving question for survey #%s',
                    $surveyId
                )
            );
        }
    }

    /**
     * @todo document me
     *
     * @param Question $question
     * @param array $data
     * @return void
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    private function applyL10n($question, $data)
    {
        foreach ($data as $language => $l10nBlock) {
            $l10n = $this->modelQuestionL10n
                ->findByAttributes([
                    'qid' => $question->qid,
                    'language' => $language
                ]);
            if (empty($l10n)) {
                throw new NotFoundException('Found no L10n object');
            }
            $l10n->setAttributes(
                [
                    'question' => $l10nBlock['question'],
                    'help'     => $l10nBlock['help'],
                    'script'   => $l10nBlock['script'] ?? ''
                ],
                false
            );
            if (!$l10n->save()) {
                throw new PersistErrorException(
                    gT('Could not store translation')
                );
            }
        }
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
        $question->setAttributes($data, false);

        //set the question_order the highest existing number +1, if no question exists for the group
        //set the question_order to 1
        $highestOrderNumber = $this->proxyQuestion
            ->getHighestQuestionOrderNumberInGroup($questionGroupId);
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
        $l10n = [];
        foreach ($survey->allLanguages as $language) {
            $l10n[$language] = new QuestionL10n();
            $l10n[$language]->setAttributes(
                [
                    'qid'      => $question->qid,
                    'language' => $language,
                    'question' => '',
                    'help'     => '',
                    'script'   => '',
                ],
                false
            );
            $l10n[$language]->save();
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
    private function updateQuestionData($question, $data)
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
     * Store new answer options.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param array $optionsArray
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerOptions($question, $optionsArray)
    {
        $i = 0;
        foreach ($optionsArray as $optionArray) {
            foreach ($optionArray as $scaleId => $data) {
                if (!isset($data['code'])) {
                    throw new Exception(
                        'code is not set in data: ' . json_encode($data)
                    );
                }
                $answer = new Answer();
                $answer->qid = $question->qid;
                $answer->code = $data['code'];
                $answer->sortorder = $i;
                $i++;
                if (isset($data['assessment'])) {
                    $answer->assessment_value = $data['assessment'];
                } else {
                    $answer->assessment_value = 0;
                }
                $answer->scale_id = $scaleId;
                if (!$answer->save()) {
                    throw new PersistErrorException(
                        gT('Could not save answer')
                    );
                }
                $answer->refresh();
                foreach ($data['answeroptionl10n'] as $lang => $answerOptionText) {
                    $l10n = new AnswerL10n();
                    $l10n->aid = $answer->aid;
                    $l10n->language = $lang;
                    $l10n->answer = $answerOptionText;
                    if (!$l10n->save()) {
                        if (!$answer->save()) {
                            throw new PersistErrorException(
                                gT('Could not save answer option')
                            );
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Save subquestion.
     * Used when survey is *not* activated.
     *
     * @param Question $question
     * @param array $subquestionsArray
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function storeSubquestions($question, $subquestionsArray)
    {
        $questionOrder = 0;
        foreach ($subquestionsArray as $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                $subquestion = new Question();
                $subquestion->sid        = $question->sid;
                $subquestion->gid        = $question->gid;
                $subquestion->parent_qid = $question->qid;
                $subquestion->question_order = $questionOrder;
                $questionOrder++;
                if (!isset($data['code'])) {
                    throw new BadRequestException(
                        'Internal error: ' .
                        'Missing mandatory field "code" for question'
                    );
                }
                $subquestion->title      = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance  = $data['relevance'];
                }
                $subquestion->scale_id   = $scaleId;
                if (!$subquestion->save()) {
                    throw new PersistErrorException(
                        gT('Could not save subquestion')
                    );
                }
                $subquestion->refresh();
                foreach ($data['subquestionl10n'] as $lang => $questionText) {
                    $l10n = new QuestionL10n();
                    $l10n->qid = $subquestion->qid;
                    $l10n->language = $lang;
                    $l10n->question = $questionText;
                    if (!$l10n->save()) {
                        throw new PersistErrorException(
                            gT('Could not save subquestion')
                        );
                    }
                }
            }
        }
    }

    /**
     * Save subquestion.
     * Used when survey *is* activated.
     *
     * @param Question $question
     * @param array $subquestionsArray
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function updateSubquestions($question, $subquestionsArray)
    {
        $questionOrder = 0;
        foreach ($subquestionsArray as $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                $subquestion = Question::model()->findByAttributes(
                    [
                        'parent_qid' => $question->qid,
                        'title'      => $data['code'],
                        'scale_id'   => $scaleId
                    ]
                );
                if (empty($subquestion)) {
                    throw new NotFoundException(
                        'Found no subquestion with code ' . $data['code']
                    );
                }
                $subquestion->sid        = $question->sid;
                $subquestion->gid        = $question->gid;
                $subquestion->parent_qid = $question->qid;
                $subquestion->question_order = $questionOrder;
                $questionOrder++;
                if (!isset($data['code'])) {
                    throw new BadRequestException(
                        'Internal error: '
                        . 'Missing mandatory field "code" for question'
                    );
                }
                $subquestion->title      = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance  = $data['relevance'];
                }
                $subquestion->scale_id   = $scaleId;
                if (!$subquestion->update()) {
                    throw new PersistErrorException(
                        gT('Could not save subquestion')
                    );
                }
                $subquestion->refresh();
                foreach ($data['subquestionl10n'] as $lang => $questionText) {
                    $l10n = QuestionL10n::model()->findByAttributes(
                        [
                            'qid' => $subquestion->qid,
                            'language' => $lang
                        ]
                    );
                    if (empty($l10n)) {
                        $l10n = new QuestionL10n();
                    }
                    $l10n->qid = $subquestion->qid;
                    $l10n->language = $lang;
                    $l10n->question = $questionText;
                    if (!$l10n->save()) {
                        throw new PersistErrorException(
                            gT('Could not save subquestion')
                        );
                    }
                }
            }
        }
    }
}
