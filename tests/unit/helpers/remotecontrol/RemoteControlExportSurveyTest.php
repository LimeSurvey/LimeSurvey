<?php

namespace ls\tests\unit\helpers\remotecontrol;

/**
 * Tests for the RemoteControl export_survey_structure and export_survey_archive functions.
 */
class RemoteControlExportSurveyTest extends BaseTest
{
    /**
     * Import the test survey (active, with responses and survey participants).
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.libraries.BigData', true);

        self::importSurvey(self::$surveysFolder . '/survey_export_responses_with_tokens.lsa');
    }

    /**
     * Exporting the survey structure returns a base64 encoded LSS which can be imported again.
     */
    public function testExportSurveyStructure()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_survey_structure($sessionKey, self::$surveyId);

        $this->assertIsString($result);
        $lss = base64_decode($result);
        $this->assertStringContainsString('<LimeSurveyDocType>Survey</LimeSurveyDocType>', $lss);
        $this->assertStringContainsString('<sid><![CDATA[' . self::$surveyId . ']]></sid>', $lss);

        $newSurveyId = $this->handler->import_survey($sessionKey, $result, 'lss');
        $this->assertIsInt($newSurveyId, 'The exported LSS could not be imported: ' . json_encode($newSurveyId));
        \Survey::model()->findByPk($newSurveyId)->delete();
    }

    /**
     * Exporting the survey archive returns an LSA with structure, responses and participants which can be imported again.
     */
    public function testExportSurveyArchive()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_survey_archive($sessionKey, self::$surveyId);

        $this->assertInstanceOf(\BigFile::class, $result);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($result->fileName));
        $this->assertNotFalse($zip->locateName('survey_' . self::$surveyId . '.lss'));
        $this->assertNotFalse($zip->locateName('survey_' . self::$surveyId . '_responses.lsr'));
        $this->assertNotFalse($zip->locateName('survey_' . self::$surveyId . '_tokens.lst'));
        $zip->close();

        $lsa = base64_encode(file_get_contents($result->fileName));
        unlink($result->fileName);
        $newSurveyId = $this->handler->import_survey($sessionKey, $lsa, 'lsa');
        $this->assertIsInt($newSurveyId, 'The exported LSA could not be imported: ' . json_encode($newSurveyId));
        \Survey::model()->resetCache();
        $newSurvey = \Survey::model()->findByPk($newSurveyId);
        $this->assertEquals(2, \SurveyDynamic::model($newSurveyId)->count(), 'The responses were not exported.');
        $this->assertEquals(2, \Token::model($newSurveyId)->count(), 'The survey participants were not exported.');
        $newSurvey->delete();
    }

    /**
     * Both functions fail with an invalid session key or an invalid survey ID.
     */
    public function testExportSurveyInvalidParameters()
    {
        $result = $this->handler->export_survey_structure('invalid', self::$surveyId);
        $this->assertSame('ERR_INVALID_SESSION', $result['error_code']);
        $result = $this->handler->export_survey_archive('invalid', self::$surveyId);
        $this->assertSame('ERR_INVALID_SESSION', $result['error_code']);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->export_survey_structure($sessionKey, 0);
        $this->assertSame('ERR_INVALID_SURVEY', $result['error_code']);
        $result = $this->handler->export_survey_archive($sessionKey, 0);
        $this->assertSame('ERR_INVALID_SURVEY', $result['error_code']);
    }

    /**
     * A user who may only export the survey structure can not export the archive with the responses and participants.
     */
    public function testExportSurveyArchiveNeedsResponsesPermission()
    {
        $userName = \Yii::app()->securityManager->generateRandomString(8);
        $userId = (int) \User::insertUser($userName, createPassword(), 'John Doe', 1, $userName . '@example.org');
        $permission = new \Permission();
        $permission->entity = 'survey';
        $permission->entity_id = self::$surveyId;
        $permission->uid = $userId;
        $permission->permission = 'surveycontent';
        $permission->read_p = 1;
        $permission->export_p = 1;
        $this->assertTrue($permission->save(), json_encode($permission->getErrors()));

        $session = new \Session();
        $session->id = \Yii::app()->securityManager->generateRandomString(32);
        $session->expire = time() + 3600;
        $session->data = $userName;
        $session->save();
        // Each RPC request starts with a new PHP session: drop the session token of the admin login from the previous tests.
        unset(\Yii::app()->session['session_token']);

        try {
            $result = $this->handler->export_survey_structure($session->id, self::$surveyId);
            $this->assertIsString($result, 'The survey structure export should be allowed: ' . json_encode($result));

            $result = $this->handler->export_survey_archive($session->id, self::$surveyId);
            $this->assertIsArray($result);
            $this->assertSame('ERR_NO_PERMISSION', $result['error_code']);
        } finally {
            $session->delete();
            \Permission::model()->deleteAllByAttributes(['uid' => $userId]);
            \User::model()->deleteByPk($userId);
            \Yii::app()->session['loginID'] = 1;
            \Yii::app()->user->setId(1);
        }
    }
}
