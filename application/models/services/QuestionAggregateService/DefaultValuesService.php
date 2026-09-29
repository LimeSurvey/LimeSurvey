<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use QuestionType;
use DefaultValue;
use DefaultValueL10n;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\PersistErrorException;

/**
 * Question Aggregate Default Values Service
 *
 * Service class for saving the default answers of a question.
 *
 * Dependencies are injected to enable mocking.
 */
class DefaultValuesService
{
    private DefaultValue $modelDefaultValue;
    private DefaultValueL10n $modelDefaultValueL10n;

    public function __construct(
        DefaultValue $modelDefaultValue,
        DefaultValueL10n $modelDefaultValueL10n
    ) {
        $this->modelDefaultValue = $modelDefaultValue;
        $this->modelDefaultValueL10n = $modelDefaultValueL10n;
    }

    /**
     * Save the default answers of a question for all survey languages.
     *
     * Default values depend on three question type properties: answerscales,
     * subquestions and hasdefaultvalues. Only these combinations of
     * answerscales and subquestions are supported:
     *  - answerscales = 1 and subquestions = 0 (e.g. List radio)
     *  - answerscales = 0 and subquestions = 1 (e.g. Multiple choice)
     *  - answerscales = 0 and subquestions = 0 (e.g. Short text, Yes/No)
     * Array questions (answerscales = 1 and subquestions = 1, or
     * subquestions = 2) are not supported yet.
     *
     * NB: Must be called after the subquestions have been saved, because
     * subquestion default values are matched by subquestion title.
     *
     * @param Question $question
     * @param array{
     *  ?defaultvalues: array<string, mixed>,
     *  ?other: array<string, array<int, string>>,
     *  ?defaultvalues_em: array<string, string>,
     *  ?samedefault: mixed
     * } $data
     * @return void
     * @throws PersistErrorException
     */
    public function save(Question $question, array $data)
    {
        $defaultAnswers = $data['defaultvalues'] ?? [];
        $other = $data['other'] ?? [];
        // No need to do anything if there are no default answers passed
        if ((empty($defaultAnswers) && empty($other)) || !$question->questionType->hasdefaultvalues) {
            return;
        }
        $expressions = $data['defaultvalues_em'] ?? [];

        // "Use same default value across languages"
        $question->same_default = empty($data['samedefault']) ? 0 : 1;
        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Could not save question')
            );
        }

        foreach ($question->survey->allLanguages as $language) {
            switch ($question->type) {
                case QuestionType::QT_L_LIST:
                case QuestionType::QT_O_LIST_WITH_COMMENT:
                case QuestionType::QT_EXCLAMATION_LIST_DROPDOWN:
                    if (isset($defaultAnswers[$language][0])) {
                        $this->storeDefaultValue($question->qid, 0, '', $language, $defaultAnswers[$language][0]);
                    }
                    if (isset($other[$language][0])) {
                        $this->storeDefaultValue($question->qid, 0, 'other', $language, $other[$language][0]);
                    }
                    break;
                case QuestionType::QT_K_MULTIPLE_NUMERICAL:
                case QuestionType::QT_M_MULTIPLE_CHOICE:
                case QuestionType::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                case QuestionType::QT_Q_MULTIPLE_SHORT_TEXT:
                    // Subquestions may have just been created, so they are
                    // matched by title instead of by qid.
                    $question->refresh();
                    foreach ($question->subquestions as $subquestion) {
                        if (isset($defaultAnswers[$language][$subquestion->title][0])) {
                            $this->storeDefaultValue(
                                $question->qid,
                                $subquestion->qid,
                                '',
                                $language,
                                $defaultAnswers[$language][$subquestion->title][0]
                            );
                        }
                    }
                    break;
                case QuestionType::QT_5_POINT_CHOICE:
                    if (isset($defaultAnswers[$language][0])) {
                        $this->storeDefaultValue($question->qid, 0, '', $language, $defaultAnswers[$language][0]);
                    }
                    break;
                case QuestionType::QT_Y_YES_NO_RADIO:
                    if (!isset($defaultAnswers[$language])) {
                        break;
                    }
                    $value = $defaultAnswers[$language] === 'EM'
                        ? ($expressions[$language] ?? '')
                        : $defaultAnswers[$language];
                    $this->storeDefaultValue($question->qid, 0, '', $language, $value);
                    break;
                default:
                    if (isset($defaultAnswers[$language]) && is_string($defaultAnswers[$language])) {
                        $this->storeDefaultValue($question->qid, 0, '', $language, $defaultAnswers[$language]);
                    }
            }
        }
    }

    /**
     * Create, update or delete a single default value for one language.
     * An empty $value removes the entry for that language, and removes the
     * default value itself once no language uses it any more.
     *
     * Based on the former Database::_updateDefaultValues().
     *
     * @param int $qid Question ID
     * @param int $sqid Subquestion ID, 0 if none
     * @param string $specialType Special type, e.g. 'other', empty string if none
     * @param string $language Language code
     * @param string $value The default value itself
     * @return void
     * @throws PersistErrorException
     */
    private function storeDefaultValue($qid, $sqid, $specialType, $language, $value)
    {
        // Only one scale is supported, see save()
        $scaleId = 0;
        $defaultValue = $this->modelDefaultValue->findByAttributes([
            'qid' => $qid,
            'sqid' => $sqid,
            'scale_id' => $scaleId,
            'specialtype' => $specialType,
        ]);

        if ((string)$value === '') {
            if ($defaultValue) {
                $this->modelDefaultValueL10n->deleteAllByAttributes([
                    'dvid' => $defaultValue->dvid,
                    'language' => $language,
                ]);
                if ($this->modelDefaultValueL10n->countByAttributes(['dvid' => $defaultValue->dvid]) == 0) {
                    // NB: $defaultValue->delete() fails because DefaultValue::primaryKey() returns an array
                    $this->modelDefaultValue->deleteByPk($defaultValue->dvid);
                }
            }
            return;
        }

        if (!$defaultValue) {
            // We use the container to create a model instance
            // allowing us to mock the model instance via
            // container configuration in unit tests
            $defaultValue = DI::getContainer()->make(DefaultValue::class);
            $defaultValue->setAttributes([
                'qid' => $qid,
                'sqid' => $sqid,
                'scale_id' => $scaleId,
            ]);
            $defaultValue->specialtype = $specialType;
            if (!$defaultValue->save()) {
                throw new PersistErrorException(
                    gT('Could not save default value')
                );
            }
        }

        $l10n = $this->modelDefaultValueL10n->findByAttributes([
            'dvid' => $defaultValue->dvid,
            'language' => $language,
        ]);
        if (!$l10n) {
            $l10n = DI::getContainer()->make(DefaultValueL10n::class);
        }
        // Saving through the model runs the LSYii_Validators XSS filter
        $l10n->setAttributes([
            'dvid' => $defaultValue->dvid,
            'language' => $language,
            'defaultvalue' => $value,
        ]);
        if (!$l10n->save()) {
            throw new PersistErrorException(
                gT('Could not save default value')
            );
        }
    }
}
