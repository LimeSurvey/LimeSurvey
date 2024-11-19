<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlGetUploadedFilesTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    /**
     * Setup.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        self::$username = getenv('ADMINUSERNAME');
        if (!self::$username) {
            self::$username = 'admin';
        }

        self::$password = getenv('PASSWORD');
        if (!self::$password) {
            self::$password = 'password';
        }

        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);
    }

    /**
     * Test the get_uploaded_files API call using response ID.
     */
    public function testGetUploadedFilesByResponseId()
    {
        $uploadedFileName = 'fu_yshu88ibxznfvbu'; // Taken from the LSA
        $this->prepareTestWithUploadedFile('survey_archive_getFileUploadTest.lsa', 'dalahorse.jpg', $uploadedFileName);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Retrieve uploaded files
        $responseId = 2;
        $result = $handler->get_uploaded_files($sessionKey, self::$surveyId, null, $responseId);
        $this->assertArrayHasKey($uploadedFileName, $result, '$result = ' . json_encode($result));

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Test the get_uploaded_files API call using tokens
     */
    public function testGetUploadedFilesByToken()
    {
        $uploadedFileName = 'fu_xtdezsfty3v5vcm'; // Taken from the LSA
        $this->prepareTestWithUploadedFile('survey_archive_getFileUploadTestClosed.lsa', 'dalahorse.jpg', $uploadedFileName);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Retrieve uploaded files
        $token = '123456';
        $result = $handler->get_uploaded_files($sessionKey, self::$surveyId, $token);
        $this->assertArrayHasKey($uploadedFileName, $result, '$result = ' . json_encode($result));

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Test the get_uploaded_files API call using tokens
     */
    public function testGetUploadedFilesByResponseIdAndToken()
    {
        $uploadedFileName = 'fu_xtdezsfty3v5vcm'; // Taken from the LSA
        $this->prepareTestWithUploadedFile('survey_archive_getFileUploadTestClosed.lsa', 'dalahorse.jpg', $uploadedFileName);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Retrieve uploaded files
        $token = '123456';
        $responseId = 4;
        $result = $handler->get_uploaded_files($sessionKey, self::$surveyId, $token, $responseId);
        $this->assertArrayHasKey($uploadedFileName, $result, '$result = ' . json_encode($result));

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Prepare the data for get_uploaded_files tests
     */
    private function prepareTestWithUploadedFile($surveyFile, $fileName, $uploadedFileName)
    {
        // Import the survey
        $filename = self::$surveysFolder . '/' . $surveyFile;
        self::importSurvey($filename);

        // Setup the test resources
        $testSid = self::$testSurvey->sid;
        exec('sudo chmod -R 777 ' . \Yii::app()->getConfig('uploaddir')); // Add permisions to ./upload directory, neede for CI pipeline
        $surveyUploadsDir = \Yii::app()->getConfig('uploaddir') . "/surveys/$testSid/files/";
        if (!is_dir($surveyUploadsDir)) {
            $dirCreated = mkdir($surveyUploadsDir, 0777, true);
            $this->assertTrue($dirCreated, "Couldn't create dir '$surveyUploadsDir'");
        }
        $file = self::$dataFolder . '/file_upload/' . $fileName;
        $this->assertTrue(file_exists($file));
        $targetFile = $surveyUploadsDir . $uploadedFileName;
        copy($file, $targetFile);
        $this->assertTrue(file_exists($targetFile));
        // Refresh metadata to make sure the latests fields are used
        \Response::model($testSid)->refreshMetaData();
    }

    /**
     * Test the get_uploaded_files API call using wrong response id and token
     */
    public function testGetUploadedFilesByWrongResponseIdAndToken()
    {
        $uploadedFileName = 'fu_xtdezsfty3v5vcm'; // Taken from the LSA
        $this->prepareTestWithUploadedFile('survey_archive_getFileUploadTestClosed.lsa', 'dalahorse.jpg', $uploadedFileName);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Retrieve uploaded files
        $token = '123456';
        $responseId = 1;
        $result = $handler->get_uploaded_files($sessionKey, self::$surveyId, $token, $responseId);
        $this->assertArrayNotHasKey($uploadedFileName, $result, '$result = ' . json_encode($result));

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Test the get_uploaded_files API call using response id and wrong token 
     */
    public function testGetUploadedFilesByResponseIdAndWrongToken()
    {
        $uploadedFileName = 'fu_xtdezsfty3v5vcm'; // Taken from the LSA
        $this->prepareTestWithUploadedFile('survey_archive_getFileUploadTestClosed.lsa', 'dalahorse.jpg', $uploadedFileName);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Retrieve uploaded files
        $token = '1234567';
        $responseId = 4;
        $result = $handler->get_uploaded_files($sessionKey, self::$surveyId, $token, $responseId);
        $this->assertArrayNotHasKey($uploadedFileName, $result, '$result = ' . json_encode($result));

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
