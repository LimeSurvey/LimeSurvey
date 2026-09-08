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

    /**
     * When filterxsshtml is enabled and the current user is not a superadmin,
     * LSYii_Validators must automatically strip XSS from quotals_message on save.
     */
    public function testSaveNewQuotaFiltersQuotaMessageBeforeSaving()
    {
        // Create a non-superadmin user so isXssFiltered() returns true,
        // which activates the LSYii_Validators XSS filter on model save.
        $userName = \Yii::app()->securityManager->generateRandomString(8);
        $regularUser = self::createUserWithPermissions([
            'full_name'  => $userName,
            'users_name' => $userName,
            'email'      => $userName . '@example.org',
            'password'   => 'testpassword123',
        ]);

        $originalLoginId    = \Yii::app()->session['loginID'] ?? null;
        $filterXssTmp       = \Yii::app()->getConfig('filterxsshtml');
        $filterForcedAllTmp = \Yii::app()->getConfig('filterxsshtml_forcedall');

        $quotaData['name'] = 'FilteredMessageQuota';
        $quotaData['qlimit'] = 0;
        $quotaData['action'] = 1;
        $quotaData['active'] = 1;
        $quotaData['autoload_url'] = 0;

        $originalQuotaLanguageSetting = $_POST['QuotaLanguageSetting'] ?? null;

        try {
            // Log in as the non-superadmin and enable XSS filtering.
            \Yii::app()->session['loginID'] = $regularUser->uid;
            \Yii::app()->setConfig('filterxsshtml_forcedall', false);
            \Yii::app()->setConfig('filterxsshtml', true);

            $_POST['QuotaLanguageSetting'] = [];
            foreach (self::$testSurvey->getAllLanguages() as $language) {
                $_POST['QuotaLanguageSetting'][$language] = [
                    'quotals_message' => '<img src=x onerror="alert(document.domain)">',
                ];
            }

            $quotaService = new Quotas(self::$testSurvey);
            $newQuota = $quotaService->saveNewQuota($quotaData);
        } finally {
            // Always restore state.
            \Yii::app()->session['loginID'] = $originalLoginId;
            \Yii::app()->setConfig('filterxsshtml', $filterXssTmp);
            \Yii::app()->setConfig('filterxsshtml_forcedall', $filterForcedAllTmp);

            if ($originalQuotaLanguageSetting === null) {
                unset($_POST['QuotaLanguageSetting']);
            } else {
                $_POST['QuotaLanguageSetting'] = $originalQuotaLanguageSetting;
            }

            $regularUser->delete();
        }

        $this->assertEquals(count($newQuota->getErrors()), 0);

        $quotaLanguageSetting = \QuotaLanguageSetting::model()->findByAttributes([
            'quotals_quota_id' => $newQuota->primaryKey,
            'quotals_language' => self::$testSurvey->language,
        ]);

        $this->assertNotNull($quotaLanguageSetting);
        $this->assertStringNotContainsString('onerror', $quotaLanguageSetting->quotals_message);
        $this->assertStringNotContainsString('alert(document.domain)', $quotaLanguageSetting->quotals_message);
    }

    public function testEditQuota()
    {
        $quotaService = new Quotas(self::$testSurvey);

        $surveyId = self::$testSurvey->sid;
        $quota = \Quota::model()->findByAttributes(['sid' => $surveyId, 'name' => 'Europe-quota']);

        $quotaData['name'] = 'UpdateQuotaName';

        // Setup $_POST data for language settings
        $originalQuotaLanguageSetting = $_POST['QuotaLanguageSetting'] ?? null;
        $_POST['QuotaLanguageSetting'] = [];
        foreach ($quota->languagesettings as $language => $languageSetting) {
            $_POST['QuotaLanguageSetting'][$language] = [
                'quotals_message' => 'Test message',
            ];
        }

        $updatedQuota = $quotaService->editQuota($quota, $quotaData);

        // Restore $_POST
        if ($originalQuotaLanguageSetting === null) {
            unset($_POST['QuotaLanguageSetting']);
        } else {
            $_POST['QuotaLanguageSetting'] = $originalQuotaLanguageSetting;
        }

        $this->assertEquals(count($updatedQuota->getErrors()), 0);
    }
}
