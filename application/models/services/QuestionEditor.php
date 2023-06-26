<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use QuestionL10n;
use Survey;
use LSYii_Application;

use LimeSurvey\Models\Services\Proxy\ProxySettingsUser;

use CException;
use LSUserException;

use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
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
    private Survey $modelSurvey;
    private ProxySettingsUser $proxySettingsUser;
    private LSYii_Application $yiiApp;
    private ExpressionManager $expressionManager;

    public function __construct(
        Permission $modelPermission,
        Question $modelQuestion,
        QuestionL10n $modelQuestionL10n,
        Survey $modelSurvey,
        ProxySettingsUser $proxySettingsUser,
        LSYii_Application $yiiApp,
        ExpressionManager $expressionManager
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionL10n = $modelQuestionL10n;
        $this->modelSurvey = $modelSurvey;
        $this->proxySettingsUser = $proxySettingsUser;
        $this->yiiApp = $yiiApp;
        $this->expressionManager = $expressionManager;
    }

    /**
     * @param <array-key, mixed> $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return array
     */
    public function save($input)
    {
        $request = App()->request;
        $iSurveyId = (int) ($input['sid'] ?? 0);

        $questionData = [];
        $questionData['question']         = $input['question'] ?? [];
        // TODO: It's l10n, not i10n.
        $questionData['questionI10N']     = $input['questionI10N'] ?? [];
        $questionData['advancedSettings'] = $input['advancedSettings'] ?? [];
        $questionData['question']['sid']  = $iSurveyId;

        $question = $this->modelQuestion->findByPk((int) $questionData['question']['qid']);

        $surveyId = $question ? $question->sid : $iSurveyId;

        // Different permission check when sid vs qid is given.
        // This double permission check is needed if user manipulates the post data.
        if (
            !$this->modelPermission->hasSurveyPermission(
            $surveyId,
            'surveycontent',
            'update'
            )
        ) {
            throw new ExceptionPermissionDenied(
                'Access denied'
            );
        }

        // Rollback at failure.
        $transaction = $this->yiiApp->db->beginTransaction();
        try {
            if ($questionData['question']['qid'] == 0) {
                $questionData['question']['qid'] = null;
                $question = $this->storeNewQuestionData($questionData['question']);
            } else {
                // Store changes to the actual question data, by either storing it, or updating an old one
                $question = $this->updateQuestionData($question, $questionData['question']);
            }

            // Apply the changes to general settings, advanced settings and translations
            $setApplied = [];

            $this->applyL10n(
                $question,
                $questionData['questionI10N']
            );

            $setApplied['advancedSettings'] = $this->unparseAndSetAdvancedOptions(
                $question,
                $questionData['advancedSettings']
            );

            $setApplied['question'] = $this->unparseAndSetGeneralOptions(
                $question,
                $questionData['question']
            );

            // save advanced attributes default values for given question type
            if (
                array_key_exists('save_as_default', $questionData['question'])
                && $questionData['question']['save_as_default'] == 'Y'
            ) {
                ProxySettingsUser::setUserSetting(
                    'question_default_values_' . $questionData['question']['type'],
                    ls_json_encode($questionData['advancedSettings'])
                );
            } elseif (
                array_key_exists('clear_default', $questionData['question'])
                && $questionData['question']['clear_default'] == 'Y'
            ) {
                ProxySettingsUser::deleteUserSetting('question_default_values_' . $questionData['question']['type']);
            }

            // Clean answer options before save.
            // NB: Still inside a database transaction.
            $question->deleteAllAnswers();
            // If question type has answeroptions, save them.
            if ($question->questionType->answerscales > 0) {
                $this->storeAnswerOptions(
                    $question,
                    $request->getPost('answeroptions')
                );
            }

            if ($question->survey->active == 'N') {
                // Clean subquestions before save.
                $question->deleteAllSubquestions();
                // If question type has subquestions, save them.
                if ($question->questionType->subquestions > 0) {
                    $this->storeSubquestions(
                        $question,
                        $request->getPost('subquestions')
                    );
                }
            } else {
                if ($question->questionType->subquestions > 0) {
                    $this->updateSubquestions(
                        $question,
                        $request->getPost('subquestions')
                    );
                }
            }
            $transaction->commit();

            // All done, redirect to edit form.
            $question->refresh();
            $this->expressionManager->setDirtyFlag();

        } catch (CException $ex) {
            $transaction->rollback();

            throw new ExceptionPersistError(
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
     * @param array $dataSet
     * @return void
     * @throws ExceptionNotFound
     * @throws ExceptionPersistError
     */
    private function applyL10n($question, $dataSet)
    {
        foreach ($dataSet as $language => $l10nBlock) {
            $l10n = $this->modelQuestionL10n
                ->findByAttributes([
                    'qid' => $question->qid,
                    'language' => $language
                ]);
            if (empty($l10n)) {
                throw new ExceptionNotFound('Found no L10n object');
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
                throw new ExceptionPersistError(
                    gT('Could not store translation')
                );
            }
        }
    }

        /**
     * Method to store and filter questionData for a new question
     *
     * todo: move to model or service class
     *
     * @param array $aQuestionData what is inside this array ??
     * @param boolean $subquestion
     * @return Question
     * @throws CHttpException
     */
    private function storeNewQuestionData($aQuestionData = null, $subquestion = false)
    {
        $iSurveyId = $aQuestionData['sid'];
        $oSurvey = $this->modelSurvey->findByPk($iSurveyId);
        $iQuestionGroupId = (int) $aQuestionData['gid'];
        $type = $this->proxySettingsUser->getUserSettingValue(
            'preselectquestiontype',
            null,
            null,
            null,
            $this->yiiApp->getConfig('preselectquestiontype')
        );

        if (isset($aQuestionData['same_default'])) {
            if ($aQuestionData['same_default'] == 1) {
                $aQuestionData['same_default'] = 0;
            } else {
                $aQuestionData['same_default'] = 1;
            }
        }

        if (!isset($aQuestionData['same_script'])) {
            $aQuestionData['same_script'] = 0;
        }

        $aQuestionData = array_merge(
            [
                'sid'        => $iSurveyId,
                'gid'        => $iQuestionGroupId,
                'type'       => $type,
                'other'      => 'N',
                'mandatory'  => 'N',
                'relevance'  => 1,
                'group_name' => '',
                'modulename' => '',
                'encrypted'  => 'N'
            ],
            $aQuestionData
        );
        unset($aQuestionData['qid']);

        if ($subquestion) {
            foreach ($oSurvey->allLanguages as $language) {
                unset($aQuestionData[$language]);
            }
        } else {
            $aQuestionData['question_order'] = getMaxQuestionOrder($iQuestionGroupId);
        }

        $oQuestion = new Question();
        $oQuestion->setAttributes($aQuestionData, false);

        //set the question_order the highest existing number +1, if no question exists for the group
        //set the question_order to 1
        $highestOrderNumber = Question::getHighestQuestionOrderNumberInGroup($iQuestionGroupId);
        if ($highestOrderNumber === null) { //this means there is no question inside this group ...
            $oQuestion->question_order = Question::START_SORTING_VALUE;
        } else {
            $oQuestion->question_order = $highestOrderNumber + 1;
        }


        if ($oQuestion == null) {
            throw new LSUserException(
                500,
                gT("Question creation failed - input was malformed or invalid"),
                0,
                null,
                true
            );
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw (new LSUserException(
                500,
                gT('Could not save question'),
                0,
                null,
                true
            ))->setDetailedErrorsFromModel($oQuestion);
        }

        $i10N = [];
        foreach ($oSurvey->allLanguages as $language) {
            $i10N[$language] = new QuestionL10n();
            $i10N[$language]->setAttributes(
                [
                    'qid'      => $oQuestion->qid,
                    'language' => $language,
                    'question' => '',
                    'help'     => '',
                    'script'   => '',
                ],
                false
            );
            $i10N[$language]->save();
        }

        return $oQuestion;
    }
}
