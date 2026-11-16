<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for getStatisticsFieldNames(), which builds the statistics field names used by the
 * RemoteControl export_statistics function (mantis #18049).
 */
class StatisticsFieldNamesTest extends TestBaseClass
{
    /**
     * Deletes the survey imported by the test, so that every test starts with a clean state.
     */
    protected function tearDown(): void
    {
        \Yii::app()->session['loginID'] = 1;
        if (self::$testSurvey) {
            \Yii::app()->db->schema->refresh();
            self::$testSurvey->delete();
            self::$testSurvey = null;
        }
        parent::tearDown();
    }

    /**
     * Survey files and the question types in them whose statistics field names are checked.
     *
     * @return array
     */
    public static function surveyProvider(): array
    {
        return [
            'Multiple numerical and multiple short text' => ['limesurvey_survey_MinMaxCompareTest.lss', ['K', 'Q']],
            'Array dual scale' => ['limesurvey_survey_677328.lss', ['1']],
            'Array (Numbers)' => ['limesurvey_survey_ArrayNumberCheckbox.lss', [':']],
            'Ranking' => ['limesurvey_survey_rankingFilterHideShow.lss', ['R']],
        ];
    }

    /**
     * Every statistics field name generated for a question must point to an existing field of the survey.
     *
     * @dataProvider surveyProvider
     * @param string $surveyFile
     * @param string[] $questionTypes
     */
    public function testFieldNamesMatchFieldMap(string $surveyFile, array $questionTypes): void
    {
        self::importSurvey(self::$surveysFolder . '/' . $surveyFile);

        $fieldMap = createFieldMap(self::$testSurvey, 'full', true, false, self::$testSurvey->language);
        $questions = array_filter(
            \Question::model()->getQuestionList(self::$surveyId),
            function ($question) use ($questionTypes) {
                return $question->parent_qid == 0 && in_array($question->type, $questionTypes);
            }
        );
        $this->assertNotEmpty($questions, 'The survey does not contain any question of the tested types.');

        foreach ($questions as $question) {
            $fieldNames = getStatisticsFieldNames(self::$surveyId, [$question], null);
            $this->assertNotEmpty($fieldNames, "No statistics field names were generated for question {$question->title}.");
            foreach ($fieldNames as $fieldName) {
                // Remove the ranking position suffix ("-1") and the leading question type letter ("KQ12_S34")
                $fieldKey = preg_replace('/-\d+$/', '', $fieldName);
                if (strcspn($fieldKey, '0123456789') > 1) {
                    $fieldKey = substr($fieldKey, 1);
                }
                $this->assertArrayHasKey(
                    $fieldKey,
                    $fieldMap,
                    "Statistics field name {$fieldName} of question {$question->title} does not match any field of the survey."
                );
            }
        }
    }
}
