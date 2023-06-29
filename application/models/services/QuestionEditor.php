<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use Survey;
use Condition;
use Answer;
use AnswerL10n;
use LSYii_Application;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorL10n,
    QuestionEditorAttributes
};

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
    private Survey $modelSurvey;
    private Condition $modelCondition;

    private QuestionEditorL10n $questionEditorL10n;
    private QuestionEditorAttributes $questionEditorAttributes;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyQuestion $proxyQuestion;
    private ProxyExpressionManager $proxyExpressionManager;
    private LSYii_Application $yiiApp;

    public function __construct(
        Permission $modelPermission,
        Question $modelQuestion,
        Survey $modelSurvey,
        Condition $modelCondition,
        QuestionEditorL10n $questionEditorL10n,
        QuestionEditorAttributes $questionEditorAttributes,
        ProxySettingsUser $proxySettingsUser,
        ProxyQuestion $proxyQuestion,
        ProxyExpressionManager $proxyExpressionManager,
        LSYii_Application $yiiApp
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelQuestion = $modelQuestion;
        $this->modelSurvey = $modelSurvey;
        $this->modelCondition = $modelCondition;
        $this->questionEditorL10n = $questionEditorL10n;
        $this->questionEditorAttributes = $questionEditorAttributes;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->proxyQuestion = $proxyQuestion;
        $this->proxyExpressionManager = $proxyExpressionManager;
        $this->yiiApp = $yiiApp;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array{
     *  sid: int,
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
     *  },
     *  ?questionL10n: array{
     *      ...<array-key, array{
     *          question: string,
     *          help: string,
     *          ?language: string,
     *          ?script: string
     *      }>
     *  },
     *  ?subquestions: array{
     *      ...<array-key, mixed>
     *  },
     *  ?answeroptions: array{
     *      ...<array-key, mixed>
     *  },
     *  ?advancedSettings: array{
     *      ?logic: array{
     *          ?min_answers: int,
     *          ?max_answers: int,
     *          ?array_filter_style: int,
     *          ?array_filter: string,
     *          ?array_filter_exclude: string,
     *          ?exclude_all_others: int,
     *          ?random_group: string,
     *          ?em_validation_q: string,
     *          ?em_validation_q_tip: array{
     *              ?en: string,
     *              ?de: string,
     *              ...<array-key, mixed>
     *          },
     *          ...<array-key, mixed>
     *      },
     *      ?display: array{
     *          ...<array-key, mixed>
     *      },
     *      ?statistics: array{
     *          ...<array-key, mixed>
     *      },
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
        $surveyId = (int) ($input['sid'] ?? 0);

        $data = [];
        $data['question']         = $input['question'] ?? [];
        $data['questionL10n']     = $input['questionL10n'] ?? [];
        $data['advancedSettings'] = $input['advancedSettings'] ?? [];
        $data['question']['sid']  = $surveyId;
        $data['question']['qid']  = $data['question']['qid'] ?? null;

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

            $this->questionEditorL10n->save(
                $question->qid,
                $data['questionL10n']
            );

            $this->questionEditorAttributes
                ->saveAdvanced(
                    $question,
                    $data['advancedSettings']
                );

            $this->questionEditorAttributes
                ->save(
                    $question,
                    $data['question']
                );

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
                if ($question->questionType->subquestions > 0) {
                    $this->storeSubquestions(
                        $question,
                        $input['subquestions'] ?? []
                    );
                }
            } else {
                if ($question->questionType->subquestions > 0) {
                    $this->updateSubquestions(
                        $question,
                        $input['subquestions'] ?? []
                    );
                }
            }
            $transaction->commit();

            // All done, redirect to edit form.
            $question->refresh();
            $this->proxyExpressionManager->setDirtyFlag();
        } catch (\Exception $e) {
            $transaction->rollback();

            throw new PersistErrorException(
                sprintf(
                    'Failed saving question for survey #%s "%s"',
                    $surveyId,
                    $e->getMessage()
                )
            );
        }

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
                $subquestion             = new Question();
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
                $subquestion->title = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance = $data['relevance'];
                }
                $subquestion->scale_id = $scaleId;
                if (!$subquestion->save()) {
                    throw new PersistErrorException(
                        gT('Could not save subquestion')
                    );
                }
                $subquestion->refresh();
                foreach ($data['subquestionl10n'] as $lang => $questionText) {
                    $this->questionEditorL10n->save(
                        array(
                            [
                                'qid' => $subquestion->qid,
                                'language' => $lang,
                                'question' => $questionText
                            ]
                        )
                    );
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
                    $this->questionEditorL10n->save(
                        array(
                            [
                                'qid' => $subquestion->qid,
                                'language' => $lang,
                                'question' => $questionText
                            ]
                        )
                    );
                }
            }
        }
    }
}
