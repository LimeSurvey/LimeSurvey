<?php

namespace ls\tests;

use Yii;

/**
 * Tests for Quota::getCompleteCount() with encrypted questions.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20730
 */
class QuotaEncryptedCompleteCountTest extends TestBaseClass
{
    /** @var \Question Encrypted list question used by the "Europe-quota" */
    private static $encryptedQuestion;

    /** @var \Question Unencrypted gender question used by the "Gender selection" quota */
    private static $plainQuestion;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');

        self::$encryptedQuestion = \Question::model()->findByAttributes(
            ['sid' => self::$surveyId, 'title' => 'G02Q03']
        );
        self::$encryptedQuestion->encrypted = 'Y';
        self::$encryptedQuestion->save();
        self::$plainQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q02']);

        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
        // Quota::getCompleteCount() checks the cached list of table names
        Yii::app()->db->schema->refresh();

        $encryptedColumn = 'Q' . self::$encryptedQuestion->qid;
        $plainColumn = 'Q' . self::$plainQuestion->qid;
        $submitdate = date('Y-m-d H:i:s');
        $responses = [
            [$encryptedColumn => 'AO04', $plainColumn => 'F', 'submitdate' => $submitdate],
            [$encryptedColumn => 'AO04', $plainColumn => 'M', 'submitdate' => $submitdate],
            [$encryptedColumn => 'AO01', $plainColumn => 'F', 'submitdate' => $submitdate],
            // Not completed: Must not be counted
            [$encryptedColumn => 'AO04', $plainColumn => 'F', 'submitdate' => null],
        ];
        foreach ($responses as $response) {
            // insertRecords() encrypts the values of encrypted questions
            \SurveyDynamic::model(self::$surveyId)->insertRecords(array_merge(['startlanguage' => 'en'], $response));
        }
    }

    /**
     * Completed responses must be counted for a quota on an encrypted question.
     *
     * @return void
     */
    public function testCompleteCountOnEncryptedQuestion(): void
    {
        $this->assertNotContains(
            'AO04',
            Yii::app()->db->createCommand()
                ->select('Q' . self::$encryptedQuestion->qid)
                ->from('{{responses_' . self::$surveyId . '}}')
                ->queryColumn(),
            'The response data was not stored encrypted.'
        );

        $quota = \Quota::model()->findByAttributes(['sid' => self::$surveyId, 'name' => 'Europe-quota']);
        $this->assertEquals(2, $quota->completeCount);
    }

    /**
     * Completed responses must still be counted for a quota on an unencrypted question.
     *
     * @return void
     */
    public function testCompleteCountOnUnencryptedQuestion(): void
    {
        $quota = \Quota::model()->findByAttributes(['sid' => self::$surveyId, 'name' => 'Gender selection']);
        $this->assertEquals(2, $quota->completeCount);
    }
}
