<?php

namespace ls\tests;

use LimeSurvey\Models\Services\Quotas;

class QuotasServiceTest extends \ls\tests\TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss';
        self::importSurvey($surveyFile);
    }

    public function testGetQuotaAnswers()
    {
        $quotaService = new Quotas(self::$testSurvey);
        $surveyId = self::$testSurvey->sid;
        $quota = \Quota::model()->findByAttributes(['sid' => $surveyId, 'name' => 'Europe-quota']);
        $quotaMember = \QuotaMember::model()->findByAttributes(['sid' => $surveyId, 'quota_id' => $quota->id]);
        $aQuestionAnswers = $quotaService->getQuotaAnswers(
            $quotaMember->qid,
            $quota->id
        );

        $this->assertNotEmpty($aQuestionAnswers);
    }

    public function testAllAnswersSelected()
    {
        $quotaService = new Quotas(self::$testSurvey);
        $surveyId = self::$testSurvey->sid;
        $quota = \Quota::model()->findByAttributes(['sid' => $surveyId, 'name' => 'Europe-quota']);
        $quotaMember = \QuotaMember::model()->findByAttributes(['sid' => $surveyId, 'quota_id' => $quota->id]);
        $aQuestionAnswers = $quotaService->getQuotaAnswers(
            $quotaMember->qid,
            $quota->id
        );
        $question = \Question::model()->findByPk($quotaMember->qid);

        $allAnswersSelected = $quotaService->allAnswersSelected($question, $aQuestionAnswers);
        $this->assertFalse($allAnswersSelected);
    }

    public function testGetQuotaStructure()
    {
        $quotaService = new Quotas(self::$testSurvey);

        $quotaStructure = $quotaService->getQuotaStructure();

        $this->assertNotEquals($quotaStructure['totalquotas'], 0);
    }

    public function testSaveNewQuota()
    {
        $quotaData['name'] = 'TestQuota';
        $quotaData['qlimit'] = 15;
        $quotaData['action'] = 1;
        $quotaData['active'] = 1;
        $quotaData['autoload_url'] = 0;

        $quotaService = new Quotas(self::$testSurvey);
        $newQuota = $quotaService->saveNewQuota($quotaData);

        $this->assertEquals(count($newQuota->getErrors()), 0);
    }

    public function testEditQuota()
    {
        $quotaService = new Quotas(self::$testSurvey);

        $surveyId = self::$testSurvey->sid;
        $quota = \Quota::model()->findByAttributes(['sid' => $surveyId, 'name' => 'Europe-quota']);

        $quotaData['name'] = 'UpdateQuotaName';

        $updatedQuota = $quotaService->editQuota($quota, $quotaData);

        $this->assertEquals(count($updatedQuota->getErrors()), 0);
    }
}
