<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyPermissions;

class SurveyPermissionsServiceTest extends \ls\tests\TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_268886_testSurveyPermissions.lss';
        self::importSurvey($surveyFile);
    }

    public function testUnknownUser()
    {
        $userId = 500;
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);

        self::assertFalse($oSurveyPermissions->addUserToSurveyPermission($userId));
    }
}
