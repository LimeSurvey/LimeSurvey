<?php
namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class QuesionListTest extends TestBaseClassWeb
{

    /**
     * Import survey in tests/surveys/
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $survey = self::$surveysFolder . 'limesurvey_survey_question_list.lss';
        self::importSurvey($survey);

        /** Login **/
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        /** Browser Login **/
        self::adminLogin($username, $password);
    }

    /**
     * Check if SideMenu contains questions.
     * @return void
     * @test
     * @uses 
     */
    public function checkSideMenuContainsQuestions(): void 
    {
        $surveyID  = self::$surveyId;
        $survey    = Survey::model()->findByPk($surveyID);
        $questions = $this->getQuestions($survey);

        $manager = Yii::app()->urlManager;
        $manager->setBaseUrl('http://' . $self::domain . '/index.php');
        $url = $manager->createUrl('admin/', [
            'sid' => $surveyID,
            'gid' => $groupID,
            'qis' => $questionID
        ]);

        // Check if sidemenu contains questions
        try {
            self::$webDriver->get($url);
            sleep(1);
            $expected = 1;
            $actual = self::$webDriver->findElements(WebDriverBy::cssSelector('.'));
            $this->assertCount($expected, $actual);
        } catch (Exception $exception) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename   = self::$screenshotsFolder . '/' . __CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $exception->getMessage()
            );
        }
    }

    /**
     * Check if Sidemenu contains the same questions like them in the imported survey.
     * @test
     * @return void
     */
    public function checkIfQuestionsAreTheSame(): void
    {
        // Check if they are the same than the questions in the imported survey
        $surveyID  = self::$surveyId;
        $survey    = Survey::model()->findByPk($surveyID);
        $questions = $this->getQuestions($survey);

        $manager = Yii::app()->urlManager;
        $manager->setBaseUrl('http://' . $self::domain . '/index.php');
        $url = $manager->createUrl('admin/', [
            'sid' => $surveyID,
            'gid' => $groupID,
            'qis' => $questionID
        ]);

        // Check if sidemenu contains questions
        try {
            self::$webDriver->get($url);
            sleep(1);
            $expected = 1;
            $actual = self::$webDriver->findElements(WebDriverBy::cssSelector('.'));
            $this->assertCount($expected, $actual);
            $this->assertEquals($questions, self::$webDriver->findElements(WebDriverBy::cssSelector('.')));
        } catch (Exception $exception) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename   = self::$screenshotsFolder . '/' . __CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $exception->getMessage()
            );
        }
    }

    /**
     * Returns questions.
     * @param Survey $survey Survey
     * @return array
     */
    private function getQuestions(Survey $survey): array
    {
        $result = [];
        $questionGroups = $survey->groups;
        foreach ($questionGroups as $questionGroup) {
            $questions = $questionGroup->questions;
            foreach ($questions as $question) {
                $result[$question->title] = $question;
            }
        }
        return $result;
    }
}