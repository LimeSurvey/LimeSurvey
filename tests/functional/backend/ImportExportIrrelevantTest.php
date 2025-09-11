<?php

namespace ls\tests;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Test import and export of survey data with irrelevant questions.
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
            // We need to give it time to export. To avoid sleeping for too long, we will wait for 1 second up to 10 times.
            for ($i = 0; $i < 10; $i++) {
                if (file_exists($exportedSurveyFile)) {
                    break;
                }
                sleep(1);
            }

            if (getenv('LOCAL_TEST')){
                $exportedSurveyFile = ROOT . '/../../../data/selenium-downloads/survey_archive_' . self::$surveyId . '.lsa';
                fwrite(STDERR, $exportedSurveyFile . "\n");
                $this->assertTrue(file_exists($exportedSurveyFile));
            } else {
                $this->assertTrue(file_exists($exportedSurveyFile));
            }

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

    function waitForDownload(string $downloadDir, string $fileName, int $timeout = 30): string
    {
        $filePath = rtrim($downloadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        $tempFile = $filePath . '.crdownload'; // Chrome’s temp extension
        $elapsed = 0;

        while ($elapsed < $timeout) {
            clearstatcache();

            // File exists and no temp file → download complete
            if (file_exists($filePath) && !file_exists($tempFile)) {
                return $filePath;
            }

            sleep(1);
            $elapsed++;
        }

        throw new WebDriverException("Download of {$fileName} not completed within {$timeout} seconds.");
    }

    /**
     * @param \Question $question
     * @return string
     */
    private function getSGQ($question)
    {
        return self::$surveyId . 'X' . $question->gid . 'X' . $question->qid;
    }
}
