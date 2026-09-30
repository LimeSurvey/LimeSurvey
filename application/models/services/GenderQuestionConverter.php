<?php

namespace LimeSurvey\Models\Services;

use CDbConnection;

/**
 * Converts questions of the removed Gender question type ('G') into
 * List (radio) questions ('L') with the answer options F (Female) and M (Male).
 *
 * The answer codes are the values the Gender question type stored, so existing
 * responses, default values, conditions and expressions keep working unchanged.
 * Only plain database commands are used, so the converter can run both from
 * the database update and after importing a survey, group or question.
 */
class GenderQuestionConverter
{
    /** @var string The type code of the removed Gender question type */
    const LEGACY_TYPE = 'G';

    /** @var CDbConnection */
    private $db;

    /**
     * @param CDbConnection $db The database connection to work on
     */
    public function __construct(CDbConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Convert all Gender questions, optionally only those of a single survey.
     *
     * @param int|null $surveyId Survey to convert, or null to convert all surveys
     * @return array<int, int> The converted question IDs as qid => sid
     */
    public function convert(?int $surveyId = null): array
    {
        $typeColumn = $this->db->quoteColumnName('type');
        $command = $this->db->createCommand()
            ->select('qid, sid')
            ->from('{{questions}}')
            ->where("{$typeColumn} = :type AND parent_qid = 0", [':type' => self::LEGACY_TYPE]);
        if ($surveyId !== null) {
            $command->andWhere('sid = :sid', [':sid' => $surveyId]);
        }
        $converted = [];
        foreach ($command->queryAll() as $question) {
            $this->convertQuestion((int) $question['qid'], (int) $question['sid']);
            $converted[(int) $question['qid']] = (int) $question['sid'];
        }
        return $converted;
    }

    /**
     * Convert a single Gender question into a List (radio) question.
     *
     * Also completes questions whose type was already switched to List (radio),
     * as the survey import does before saving the question.
     *
     * @param int $qid The question ID
     * @param int $sid The survey ID of the question
     * @return void
     */
    public function convertQuestion(int $qid, int $sid): void
    {
        $existingAnswers = $this->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{answers}}')
            ->where('qid = :qid', [':qid' => $qid])
            ->queryScalar();
        $type = $this->db->createCommand()
            ->select('type')
            ->from('{{questions}}')
            ->where('qid = :qid', [':qid' => $qid])
            ->queryScalar();
        if ($existingAnswers && $type !== self::LEGACY_TYPE) {
            // Already converted, keep the question theme chosen back then
            return;
        }
        if (!$existingAnswers) {
            $languages = $this->getQuestionLanguages($qid, $sid);
            // Same order as the Gender question type displayed them
            $options = [
                'F' => function ($language) {
                    return gT('Female', 'unescaped', $language);
                },
                'M' => function ($language) {
                    return gT('Male', 'unescaped', $language);
                },
            ];
            $sortOrder = 0;
            foreach ($options as $code => $getLabel) {
                $this->db->createCommand()->insert('{{answers}}', [
                    'qid' => $qid,
                    'code' => $code,
                    'sortorder' => $sortOrder++,
                    'assessment_value' => 0,
                    'scale_id' => 0,
                ]);
                $aid = (int) $this->getLastInsertID('{{answers}}');
                foreach ($languages as $language) {
                    $this->db->createCommand()->insert('{{answer_l10ns}}', [
                        'aid' => $aid,
                        'answer' => $getLabel($language),
                        'language' => $language,
                    ]);
                }
            }
        }

        $this->db->createCommand()->update(
            '{{questions}}',
            [
                'type' => 'L',
                'question_theme_name' => $this->getQuestionThemeName($qid),
            ],
            'qid = :qid',
            [':qid' => $qid]
        );
        // The display type (button group / radio list) is expressed by the question theme now
        $this->db->createCommand()->delete(
            '{{question_attributes}}',
            'qid = :qid AND attribute = :attribute',
            [':qid' => $qid, ':attribute' => 'display_type']
        );
    }

    /**
     * Get the ID of the last inserted row, reliable across database drivers.
     *
     * @param string $tableName The table the row was inserted into, needed for Postgres and MSSQL
     * @return string
     */
    private function getLastInsertID(string $tableName): string
    {
        $driver = $this->db->getDriverName();
        if ($driver == 'mysql' || $driver == 'mysqli') {
            return (string) $this->db->getLastInsertID();
        }
        return (string) $this->db->getCommandBuilder()->getLastInsertID($tableName);
    }

    /**
     * Get the question theme matching the display type the Gender question used.
     *
     * @param int $qid The question ID
     * @return string 'listradio' for the radio list display, otherwise 'bootstrap_buttons'
     */
    private function getQuestionThemeName(int $qid): string
    {
        $displayType = $this->db->createCommand()
            ->select('value')
            ->from('{{question_attributes}}')
            ->where('qid = :qid AND attribute = :attribute', [':qid' => $qid, ':attribute' => 'display_type'])
            ->queryScalar();
        return ((string) $displayType === '1') ? 'listradio' : 'bootstrap_buttons';
    }

    /**
     * Get the languages the question is available in.
     *
     * Falls back to the survey languages when the question has no translations.
     *
     * @param int $qid The question ID
     * @param int $sid The survey ID of the question
     * @return string[]
     */
    private function getQuestionLanguages(int $qid, int $sid): array
    {
        $languages = $this->db->createCommand()
            ->selectDistinct('language')
            ->from('{{question_l10ns}}')
            ->where('qid = :qid', [':qid' => $qid])
            ->queryColumn();
        if (!empty($languages)) {
            return $languages;
        }
        $survey = $this->db->createCommand()
            ->select('language, additional_languages')
            ->from('{{surveys}}')
            ->where('sid = :sid', [':sid' => $sid])
            ->queryRow();
        if (empty($survey)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_merge(
            [$survey['language']],
            explode(' ', trim((string) $survey['additional_languages']))
        ))));
    }
}
