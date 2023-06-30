<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use Answer;
use AnswerL10n;
use CDbConnection;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorQuestion,
    QuestionEditorL10n,
    QuestionEditorAttributes
};

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyExpressionManager
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

    private QuestionEditorQuestion $questionEditorQuestion;
    private QuestionEditorL10n $questionEditorL10n;
    private QuestionEditorAttributes $questionEditorAttributes;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyExpressionManager $proxyExpressionManager;
    private CDbConnection $yiiDb;

    public function __construct(
        QuestionEditorQuestion $questionEditorQuestion,
        QuestionEditorL10n $questionEditorL10n,
        QuestionEditorAttributes $questionEditorAttributes,
        Permission $modelPermission,
        Question $modelQuestion,
        ProxySettingsUser $proxySettingsUser,
        ProxyExpressionManager $proxyExpressionManager,
        CDbConnection $yiiDb
    ) {
        $this->questionEditorQuestion = $questionEditorQuestion;
        $this->questionEditorL10n = $questionEditorL10n;
        $this->modelPermission = $modelPermission;
        $this->modelQuestion = $modelQuestion;
        $this->questionEditorAttributes = $questionEditorAttributes;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->proxyExpressionManager = $proxyExpressionManager;
        $this->yiiDb = $yiiDb;
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
        $transaction = $this->yiiDb->beginTransaction();
        try {
            $question = $this->questionEditorQuestion
                ->save($data);

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

            $this->saveDefaults($data);

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
     * Store new answer options.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param array $optionsArray
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerOptions(Question $question, $optionsArray)
    {
        $count = 0;
        foreach ($optionsArray as $optionArray) {
            $this->storeAnswerOption(
                $question,
                $optionArray,
                $count
            );
        }
        return true;
    }

    /**
     * Store new answer options.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param array $optionsArray
     * @param int &$count
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerOption(Question $question, $optionArray, &$count)
    {
        foreach ($optionArray as $scaleId => $data) {
            if (!isset($data['code'])) {
                throw new Exception(
                    'code is not set in data: ' . json_encode($data)
                );
            }
            $answer = new Answer();
            $answer->qid = $question->qid;
            $answer->code = $data['code'];
            $answer->sortorder = $count;
            $count++;
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
            foreach (
                $data['answeroptionl10n']
                as $language => $answerOptionText
            ) {
                $this->storeAnswerL10n(
                    $answer,
                    $language,
                    $answerOptionText
                );
            }
        }
    }

    /**
     * Store new answer L10n
     *
     * @param Answer $answer
     * @param string $language
     * @param string $text
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerL10n(Answer $answer, $language, $text)
    {
        $l10n = new AnswerL10n();
        $l10n->aid = $answer->aid;
        $l10n->language = $language;
        $l10n->answer = $text;
        if (!$l10n->save()) {
            throw new PersistErrorException(
                gT('Could not save answer option')
            );
        }
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
    private function storeSubquestions(Question $question, $subquestionsArray)
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
                $this->updateSubquestionL10n(
                    $subquestion,
                    $data['subquestionl10n']
                );
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
    private function updateSubquestions(Question $question, $subquestionsArray)
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
                $this->updateSubquestionL10n(
                    $subquestion,
                    $data['subquestionl10n']
                );
            }
        }
    }

    /**
     * Save subquestion L10n
     *
     * @param Question $question
     * @param string $language
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function updateSubquestionL10n(Question $subquestion, $data)
    {
        foreach ($data as $language => $questionText) {
            $this->questionEditorL10n->save(
                $subquestion->qid,
                array(
                    [
                        'qid' => $subquestion->qid,
                        'language' => $language,
                        'question' => $questionText
                    ]
                )
            );
        }
    }
}
