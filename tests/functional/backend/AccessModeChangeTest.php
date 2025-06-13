<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyAccessModeService;
use Survey;
use Permission;

class AccessModeChangeTest extends TestBaseClass
{
    protected $survey = null;

    protected SurveyAccessModeService $surveyAccessModeService;

    protected function getSurvey($sid = null)
    {
        if (!$this->survey) {
            $this->survey = Survey::model()->findByPk($sid);
        }
        return $this->survey;
    }
    protected function importTheFile($filename)
    {
        $surveyFile = self::$surveysFolder . '/' . $filename;
        $result = importSurveyFile($surveyFile, false);
        $this->assertTrue(is_array($result) && isset($result['newsid']));
        $this->surveyAccessModeService = new SurveyAccessModeService(
            Permission::model(),
            $this->getSurvey($result['newsid']),
            App(),
            true
        );
        return $result;
    }

    public function testAccessModeChangeOpenToClosedCreatingTokensTable()
    {
        $result = $this->importTheFile('access_modes_797496.lsa');
        $this->assertTrue($result['access_mode'] === SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->assertFalse($this->getSurvey()->hasTokensTable);
        $this->surveyAccessModeService->changeAccessMode($this->getSurvey()->sid, SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->getSurvey()->refresh();
        $this->assertTrue($this->getSurvey()->access_mode === SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->assertTrue($this->surveyAccessModeService->getTokenTableAction() === SurveyAccessModeService::$TOKEN_TABLE_CREATED);
    }

    public function testAccessModeChangeClosedToOpenDroppingTokensTable()
    {
        $result = $this->importTheFile('access_modes_368399.lsa');
        $this->assertTrue($result['access_mode'] === SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->assertTrue($this->getSurvey()->hasTokensTable);
        $this->surveyAccessModeService->changeAccessMode($this->getSurvey()->sid, SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->getSurvey()->refresh();
        $this->assertTrue($this->getSurvey()->access_mode === SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->assertTrue($this->surveyAccessModeService->getTokenTableAction() === SurveyAccessModeService::$TOKEN_TABLE_DROPPED);
    }

    public function testAccessModeChangeOpenToClosedKeepingTokensTable()
    {
        $result = $this->importTheFile('access_modes_976917.lsa');
        $this->assertTrue($result['access_mode'] === SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->assertTrue($this->getSurvey()->hasTokensTable);
        $this->surveyAccessModeService->changeAccessMode($this->getSurvey()->sid, SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->getSurvey()->refresh();
        $this->assertTrue($this->getSurvey()->access_mode === SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->assertTrue($this->surveyAccessModeService->getTokenTableAction() === SurveyAccessModeService::$TOKEN_TABLE_NO_ACTION);
    }

    public function testAccessModeChangeClosedToOpenKeepingTokensTable()
    {
        $result = $this->importTheFile('access_modes_553745.lsa');
        $this->assertTrue($result['access_mode'] === SurveyAccessModeService::$ACCESS_TYPE_CLOSED);
        $this->assertTrue($this->getSurvey()->hasTokensTable);
        $this->surveyAccessModeService->changeAccessMode($this->getSurvey()->sid, SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->getSurvey()->refresh();
        $this->assertTrue($this->getSurvey()->access_mode === SurveyAccessModeService::$ACCESS_TYPE_OPEN);
        $this->assertTrue($this->surveyAccessModeService->getTokenTableAction() === SurveyAccessModeService::$TOKEN_TABLE_NO_ACTION);
    }
}