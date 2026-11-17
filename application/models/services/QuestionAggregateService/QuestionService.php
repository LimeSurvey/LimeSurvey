<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use QuestionType;
use QuestionAttribute;
use Survey;
use Answer;
use AnswerL10n;
use LimeExpressionManager;
use Condition;
use LSYii_Application;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\{
    QuestionAttributeHelper,
    Proxy\ProxySettingsUser,
    Proxy\ProxyQuestion,
    Exception\PersistErrorException,
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\QuestionHasConditionsException
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
    private QuestionAttributeHelper $questionAttributeHelper;
    private ProxySettingsUser $proxySettingsUser;
    private ProxyQuestion $proxyQuestion;
    private LSYii_Application $yiiApp;
    /** @var array<string, string>|null Answer option layout per question type, see getAnswerOptionLayouts() */
    private ?array $answerOptionLayouts = null;
    /** @var int[] The questions whose conditions the last update deleted, see getChangedDependentQuestionIds() */
    private array $changedDependentQuestionIds = [];

    public function __construct(
        Question $modelQuestion,
        Survey $modelSurvey,
        Condition $modelCondition,
        L10nService $l10nService,
        QuestionAttributeHelper $questionAttributeHelper,
        ProxySettingsUser $proxySettingsUser,
        ProxyQuestion $proxyQuestion,
        LSYii_Application $yiiApp
    ) {
        $this->modelQuestion = $modelQuestion;
        $this->modelSurvey = $modelSurvey;
        $this->modelCondition = $modelCondition;
        $this->l10nService = $l10nService;
        $this->questionAttributeHelper = $questionAttributeHelper;
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
     *  },
     *  ?questionposition: string
     * } $input questionposition is only used for a new question: Empty for at the end, 0 for at the beginning, else the wanted question_order
     * @return Question
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws QuestionHasConditionsException
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
                $data['question'],
                false,
                $input['questionposition'] ?? null
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
     * @param int|string|null $position Empty for at the end, 0 for at the beginning, else the wanted question_order
     * @return Question
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    private function storeNewQuestionData($data = null, $subQuestion = false, $position = null)
    {
        $data = $data ?? [];
        $surveyId = $data['sid'] ?? 0;
        $survey = $this->modelSurvey
            ->findByPk($surveyId);
        $questionGroupId = $data['gid'] ?? 0;
        $this->checkGroupAndParentOfSurvey((int) $surveyId, $data);
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

        $question = $this->saveQuestionData($data, $questionGroupId, $position);

        $this->initL10nService($survey, $question->qid);

        return $question;
    }

    /**
     * Save question data
     *
     * @param array $data
     * @param int $questionGroupId
     * @param int|string|null $position Empty for at the end, 0 for at the beginning, else the wanted question_order
     * @return Question
     * @throws PersistErrorException
     */
    private function saveQuestionData($data, $questionGroupId, $position = null)
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
        } elseif (is_numeric($position) && (int) $position >= 0 && (int) $position <= $highestOrderNumber) {
            // Insert at the wanted position (0 means at the beginning) and move the following questions down
            $question->question_order = max((int) $position, Question::START_SORTING_VALUE);
            $this->proxyQuestion->increaseAllOrderNumbersForGroup(
                $questionGroupId,
                $question->question_order
            );
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
     * @param Question $question The question to update
     * @param array $data The new question data
     * @return Question The updated question
     * @throws NotFoundException If the group or parent question is not in the survey
     * @throws QuestionHasConditionsException If "Other" is switched off while conditions use it (see Question::validateOtherNotUsedInConditions())
     * @throws PersistErrorException If the question could not be saved
     */
    private function updateQuestionData(Question $question, $data)
    {
        $data = $this->normalizeQuestionFlags($data);

        $this->checkGroupAndParentOfSurvey((int) $question->sid, $data, $question);

        $originalRelevance = $question->relevance;
        $originalTitle = $question->title;
        $oldType = (string) $question->type;
        $newType = $this->getNewType($question, $data);
        if (isset($data['type'])) {
            // The validation must see the type that is saved: It switches "Other" off for a type without it
            $data['type'] = $newType;
        }
        $this->changedDependentQuestionIds = [];

        $question->setAttributes($data, false);

        $removeAnswers = $oldType !== $newType && !$this->typeChangeKeepsAnswerOptions($oldType, $newType);
        $removeOther = $oldType !== $newType && !$this->typeChangeKeepsOther($oldType, $newType);
        if ($removeAnswers || $removeOther) {
            // Validate before deleting, so a refused update keeps the answer options. The conditions
            // on "Other" are deleted by the type change: Their refusal doesn't count, save() validates again
            $question->validate();
            if ($removeOther && !empty($question->getOtherConditionQuestionCodes())) {
                $question->clearErrors('other');
            }
            if ($question->hasErrors()) {
                throw new PersistErrorException(
                    gT('Update failed, could not save.')
                );
            }
            $this->changedDependentQuestionIds = $this->removeAnswersAndDependentConditions(
                (int) $question->qid,
                (int) $question->sid,
                $oldType,
                $removeAnswers,
                $removeOther
            );
        }

        if (!$question->save()) {
            // The validation refuses to switch "Other" off while conditions use it
            if (!empty($question->getOtherConditionQuestionCodes())) {
                throw new QuestionHasConditionsException(
                    $question->getError('other'),
                    PersistErrorException::CODE_CHANGE_REFUSED
                );
            }
            throw new PersistErrorException(
                gT('Update failed, could not save.')
            );
        }

        if ($question->title !== $originalTitle) {
            $this->upgradeDependentQuestionRelevance($question->qid);
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
     * Checks that the group and parent question set in the data belong to the survey.
     *
     * Note: The permission was only checked on this survey.
     *
     * @param int $surveyId the survey of the question
     * @param array $data the question data
     * @param Question|null $question the existing question, null for a new one
     * @return void
     * @throws NotFoundException
     */
    private function checkGroupAndParentOfSurvey(int $surveyId, array $data, ?Question $question = null)
    {
        $groupId = (int) ($data['gid'] ?? 0);
        if (
            !empty($groupId)
            && (empty($question) || $groupId != $question->gid)
            && !\QuestionGroup::model()->exists('gid = :gid AND sid = :sid', [':gid' => $groupId, ':sid' => $surveyId])
        ) {
            throw new NotFoundException(gT('Invalid group ID'));
        }
        $parentQid = (int) ($data['parent_qid'] ?? 0);
        if (
            !empty($parentQid)
            && (empty($question) || $parentQid != $question->parent_qid)
            && !Question::model()->exists('qid = :qid AND sid = :sid', [':qid' => $parentQid, ':sid' => $surveyId])
        ) {
            throw new NotFoundException(gT('Invalid question ID'));
        }
    }

    /**
     * Returns the type the question has after the update: An active survey keeps
     * the stored type, as Question::beforeSave() does, so nothing may be deleted
     * or refused for a type change that isn't saved.
     *
     * @param Question $question The question before the update
     * @param array $data The question data of the update
     * @return string The question type after the update
     */
    private function getNewType(Question $question, array $data): string
    {
        $newType = (string) ($data['type'] ?? $question->type);
        if ($newType === (string) $question->type) {
            return $newType;
        }
        $survey = Survey::model()->findByPk($question->sid);
        if (!empty($survey) && $survey->active !== 'N') {
            return (string) $question->type;
        }
        return $newType;
    }

    /**
     * Returns the questions whose conditions the last update deleted with an
     * incompatible type change, so the editor can show their new conditions.
     *
     * @return int[] The question IDs, empty if no conditions were deleted
     */
    public function getChangedDependentQuestionIds(): array
    {
        return $this->changedDependentQuestionIds;
    }

    /**
     * @param array $data
     * @return array
     */
    private function normalizeQuestionFlags($data)
    {
        // @todo something wrong in frontend ... (?what is wrong?)
        if (isset($data['same_default'])) {
            $data['same_default'] = $data['same_default'] == 1 ? 0 : 1;
        }

        if (!isset($data['same_script'])) {
            $data['same_script'] = 0;
        }

        return $data;
    }

    /**
     * Checks whether a type change keeps the answer options (same answer option layout).
     *
     * @param string $oldType The current question type
     * @param string $newType The new question type
     * @return bool True if the answer options and the conditions on them are kept
     */
    private function typeChangeKeepsAnswerOptions(string $oldType, string $newType): bool
    {
        $layouts = $this->getAnswerOptionLayouts();
        return isset($layouts[$oldType], $layouts[$newType])
            && $layouts[$oldType] === $layouts[$newType];
    }

    /**
     * Checks whether a type change keeps the conditions on the "Other" option: Both types
     * have "Other" in the same form, as an answer option (e.g. List (radio) and List
     * (dropdown)) or as a subquestion (Multiple choice with and without comments).
     *
     * @param string $oldType The current question type
     * @param string $newType The new question type
     * @return bool True if the conditions on "Other" are kept, also if the current type has no "Other"
     */
    private function typeChangeKeepsOther(string $oldType, string $newType): bool
    {
        $types = QuestionType::modelsAttributes();
        if (empty($types[$oldType]['other'])) {
            return true;
        }
        if (empty($types[$newType]['other'])) {
            return false;
        }
        return $types[$oldType]['answerscales'] === $types[$newType]['answerscales']
            && $types[$oldType]['subquestions'] === $types[$newType]['subquestions'];
    }

    /**
     * Returns the answer option layout (answer scales and subquestions) of each type with answer options.
     *
     * A type change between types with the same layout keeps the answer options.
     *
     * @return array<string, string> Layout key indexed by question type code.
     */
    public function getAnswerOptionLayouts(): array
    {
        if ($this->answerOptionLayouts === null) {
            $this->answerOptionLayouts = [];
            foreach (QuestionType::modelsAttributes() as $type => $settings) {
                if ($settings['answerscales'] > 0) {
                    $this->answerOptionLayouts[$type] = $settings['answerscales'] . '-' . $settings['subquestions'];
                }
            }
        }
        return $this->answerOptionLayouts;
    }

    /**
     * Deletes the answer options of a question and the conditions on them, and/or
     * the conditions on its "Other" option.
     *
     * @param int $questionId Question ID.
     * @param int $surveyId The survey of the question
     * @param string $oldType The type of the question before the change
     * @param bool $removeAnswers True to delete the answer options and the conditions on them
     * @param bool $removeOther True to delete the conditions on "Other"
     * @return int[] The questions whose conditions were deleted
     */
    private function removeAnswersAndDependentConditions($questionId, $surveyId, $oldType, bool $removeAnswers, bool $removeOther)
    {
        $answers = $removeAnswers ? Answer::model()->findAll('qid = :qid', [':qid' => $questionId]) : [];
        $qids = [];
        foreach ($answers as $answer) {
            $conditions = Condition::model()->findAll(
                'cqid = :qid and value = :title',
                [':qid' => $questionId, ':title' => $answer->code]
            );
            foreach ($conditions as $condition) {
                $qids[$condition->qid] = true;
                $condition->delete();
            }
            AnswerL10n::model()->deleteAll('aid = :aid', [':aid' => $answer->aid]);
            $answer->delete();
        }
        // "Other" of the old type: Its answer option (value -oth-) and its text field
        $otherConditions = [];
        if ($removeOther) {
            [$where, $params] = Question::getOtherConditionsCriteria((int) $questionId, (int) $surveyId, (string) $oldType);
            $otherConditions = $this->yiiApp->db->createCommand()
                ->select('c.cid, c.qid')
                ->from('{{conditions}} c')
                ->join('{{questions}} q', 'q.qid = c.qid')
                ->where($where, $params)
                ->queryAll();
        }
        if (!empty($otherConditions)) {
            Condition::model()->deleteByPk(array_column($otherConditions, 'cid'));
            foreach ($otherConditions as $condition) {
                $qids[$condition['qid']] = true;
            }
        }

        $dependentQuestionIds = array_map('intval', array_keys($qids));
        if (!empty($dependentQuestionIds)) {
            // Without conditions left, the upgrade keeps the old relevance: Reset it for all of them
            // in one statement, as RevertUpgradeConditionsToRelevance() would
            $this->yiiApp->db->createCommand()->update(
                '{{questions}}',
                ['relevance' => '1'],
                [
                    'and',
                    ['in', 'qid', $dependentQuestionIds],
                    'NOT EXISTS (SELECT 1 FROM {{conditions}} c WHERE c.qid = {{questions}}.qid)',
                ]
            );
        }
        // The relevance is built from the conditions of one question at a time
        foreach ($dependentQuestionIds as $qid) {
            LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        }
        return $dependentQuestionIds;
    }

    /**
     * @param int $questionId
     * @return void
     */
    private function upgradeDependentQuestionRelevance($questionId)
    {
        $dependentConditions = Condition::model()->findAllByAttributes([
            'cqid' => $questionId
        ]);
        $dependentQuestionIds = [];
        foreach ($dependentConditions as $condition) {
            $dependentQuestionIds[(int) $condition->qid] = true;
        }
        foreach (array_keys($dependentQuestionIds) as $dependentQuestionId) {
            LimeExpressionManager::UpgradeConditionsToRelevance(null, $dependentQuestionId);
        }
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
            $advancedSettings = $data['advancedSettings'] ?? [];
            if (!empty($data['loadCurrentAdvancedSettings'])) {
                $advancedSettings = $this->getCurrentAdvancedSettings(
                    (int) $data['question']['qid']
                );
            }
            $this->proxySettingsUser->setUserSetting(
                'question_default_values_'
                . $data['question']['type'],
                ls_json_encode(
                    $advancedSettings
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
     * Return the currently persisted question attributes in the same shape as
     * advancedSettings. Patch requests update attributes independently from
     * the question, so action-only requests do not contain advancedSettings.
     */
    private function getCurrentAdvancedSettings(int $questionId): array
    {
        $attributes = [];
        foreach ($this->getQuestionAttributes($questionId) as $attribute) {
            $attributes[0][$attribute->attribute][$attribute->language] = $attribute->value;
        }
        return $attributes;
    }

    /**
     * Return the current user's saved advanced attribute defaults for a
     * question type, flattened to the API's attribute => language => value
     * representation.
     */
    public function getDefaultAttributeValues(string $questionType): array
    {
        return $this->questionAttributeHelper
            ->getUserDefaultsForQuestionType($questionType);
    }

    /**
     * Return the current user's saved attribute defaults indexed by question type.
     */
    public function getDefaultAttributeValuesByQuestionType(): array
    {
        return $this->questionAttributeHelper
            ->getUserDefaultsByQuestionType();
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
