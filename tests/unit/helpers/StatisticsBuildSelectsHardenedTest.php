<?php

namespace ls\tests;

use Question;
use Survey;
use Yii;

/**
 * Statistics filters (buildSelects) : with hardened encryption, the posted fields of encrypted questions
 * are ignored, a condition on them could never match (random nonce) (#19645).
 */
class StatisticsBuildSelectsHardenedTest extends TestBaseClass
{
    /** @var int */
    private static $encryptedQid;

    /** @var int */
    private static $plainQid;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');
        $encrypted = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G02Q03']);
        Question::model()->updateByPk($encrypted->qid, ['encrypted' => 'Y']);
        self::$encryptedQid = (int) $encrypted->qid;
        self::$plainQid = (int) Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q02'])->qid;

        $activator = new \SurveyActivator(Survey::model()->findByPk(self::$surveyId));
        $activator->activate();
        Yii::app()->db->schema->refresh();
        Yii::app()->loadHelper('admin.statistics');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

    /**
     * Filter on the encrypted question and the plain question, with this encryption method
     * @param string $encryptionMethod
     * @return string[] the conditions
     */
    private function buildSelects(string $encryptionMethod): array
    {
        Survey::model()->updateByPk(self::$surveyId, ['encryption_method' => $encryptionMethod]);
        Survey::model()->resetCache();
        $_POST = [
            'Q' . self::$encryptedQid => ['AO04'],
            'Q' . self::$plainQid => ['F'],
        ];
        return (array) buildSelects([], self::$surveyId, 'en');
    }

    /** Hardened : the condition of the encrypted question is not built, the plain one is. */
    public function testHardenedIgnoresTheEncryptedQuestion(): void
    {
        $selects = implode(' ', $this->buildSelects('H'));

        $this->assertStringNotContainsString('Q' . self::$encryptedQid, $selects);
        $this->assertStringContainsString('Q' . self::$plainQid, $selects);
    }

    /** Basic : the encrypted question is filtered with its encrypted value, as before. */
    public function testBasicFiltersTheEncryptedQuestion(): void
    {
        $selects = implode(' ', $this->buildSelects('B'));

        $this->assertStringContainsString('Q' . self::$encryptedQid, $selects);
        $this->assertStringContainsString(\LSActiveRecord::encryptSingle('AO04', 'B'), $selects);
        $this->assertStringContainsString('Q' . self::$plainQid, $selects);
    }
}
