<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Test import and export of survey data with irrelevant questions.
 *
 * @group import-export
 */
class ImportExportIrrelevantTest extends TestBaseClassWeb
{
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     * Make sure that LSA import properly differentiates between irrelevant questions and empty answers.
     */
    public function testImportIrrelevantQuestions()
    {
        /**
         * Overview:
         * 1. Import the structure of the survey with irrelevant and relevant questions.
         * 2. Activate the survey.
         * 3. Take the survey without leaving the relevant question empty.
         * 4. Export the LSA file.
         * 5. Import the LSA file.
         * 6. Check that the response has the correct values:
         *    - The irrelevant question should be null.
         *    - The relevant question should be an empty string.
         */

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_478651_import_export_irrelevant.lss';
        self::importSurvey($surveyFile);

        self::$testHelper->activateSurvey(self::$surveyId);

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
            ]
        );

        try {
            // Run the survey.
            self::$webDriver->get($url);

            // Click next.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // Check that we see completed text.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertNotEmpty($completed);

            // Check the response in the database.
            $questions = $this->getAllSurveyQuestions();
            $response = \Response::model(self::$surveyId)->findByPk(1);
            $this->assertNotEmpty($response);
            $this->assertNull($response->{$this->getSGQ($questions['Q01'])});
            $this->assertNotNull($response->{$this->getSGQ($questions['Q02'])});

            // The response is successfully created.
            // Now, we export the LSA, import it, and check the imported response.

            // Go to survey overview.
            $url = $urlMan->createUrl('surveyAdministration/view/surveyid/' . self::$surveyId);
            self::$webDriver->get($url);

            // Open the export dialog
            $exportButton = self::$webDriver->findElement(WebDriverBy::cssSelector('[data-bs-target="#selector__exportTypeSelector-modal"]'));
            $exportButton->click();

            // Select LSA option
            $lsaOption = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('a.selector__Item--select-exportTypeSelector[data-selector="surveyarchive"]')
                )
            );
            $lsaOption->click();

            // Export LSA
            $confirmExportButton = self::$webDriver->findElement(WebDriverBy::id('selector__select-this-exportTypeSelector'));
            $confirmExportButton->click();

            $exportedSurveyFile = ROOT . '/tmp/survey_archive_' . self::$surveyId . '.lsa';
            $lastSize = -1;
            self::$webDriver->wait(10, 500)->until(
                function () use ($exportedSurveyFile, &$lastSize) {
                    if (!file_exists($exportedSurveyFile)) {
                        return false;
                    }
                    clearstatcache(true, $exportedSurveyFile);
                    $size = filesize($exportedSurveyFile);
                    // Wait until file size is stable (non-zero and unchanged between polls)
                    // to avoid reading a partially written ZIP/LSA archive
                    if ($size <= 0 || $size !== $lastSize) {
                        $lastSize = $size;
                        return false;
                    }
                    // Verify the file is a valid ZIP archive (LSA files are ZIPs with a different extension).
                    // The ZIP central directory is written last, so a stable size alone is not sufficient.
                    $zip = new \ZipArchive();
                    $result = $zip->open($exportedSurveyFile);
                    if ($result !== true) {
                        return false;
                    }
                    $zip->close();
                    return true;
                },
                'Export file was not created or is not a valid ZIP/LSA archive within 10 seconds'
            );
            $this->assertFileExists($exportedSurveyFile);

            // Import LSA
            self::importSurvey($exportedSurveyFile);

            // Check the response in the database.
            $questions = $this->getAllSurveyQuestions();
            $response = \Response::model(self::$surveyId)->findByPk(1);
            $this->assertNotEmpty($response);
            $this->assertNull($response->{$this->getSGQ($questions['Q01'])});
            $this->assertNotNull($response->{$this->getSGQ($questions['Q02'])});
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * @param \Question $question
     * @return string
     */
    private function getSGQ($question)
    {
        return 'Q' . $question->qid;
    }
}
