<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-16
 */
class DateTimeDefaultAnswerExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;

    /**
     * @var int
     */
    public static $surveyId = null;

    /**
     * Import survey in tests/surveys/.
     */
    public static function setupBeforeClass()
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.replacements_helper', true);
        \Yii::import('application.helpers.surveytranslator_helper', true);
        \Yii::import('application.helpers.admin.import_helper', true);
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        \Yii::import('application.helpers.qanda_helper', true);
        \Yii::app()->loadHelper('admin/activate');

        \Yii::app()->session['loginID'] = 1;

        self::$testHelper = new TestHelper();

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_454287.lss';
        if (!file_exists($surveyFile)) {
            die('Fatal error: found no survey file');
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        $result = importSurveyFile(
            $surveyFile,
            $translateLinksFields,
            $newSurveyName,
            null
        );
        if ($result) {
            self::$surveyId = $result['newsid'];
        } else {
            die('Fatal error: Could not import survey');
        }
    }

    /**
     * Destroy what had been imported.
     */
    public static function teardownAfterClass()
    {
        $result = \Survey::model()->deleteSurvey(self::$surveyId, true);
        if (!$result) {
            die('Fatal error: Could not clean up survey ' . self::$surveyId);
        }
    }

    /**
     * Test the question with wrong default answer expression.
     * @group expr
     */
    public function testWrongDefaultAnswerExpression()
    {
        global $thissurvey;
        $thissurvey = self::$surveyId;

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('G1Q00005', self::$surveyId);

        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new \CController('dummyid'));
        buildsurveysession(self::$surveyId);
        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $result = \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            $surveyMode,
            $surveyOptions,
            false,
            $LEMdebugLevel
        );
        $this->assertEquals(
            [
                'hasNext' => 1,
                'hasPrevious' => null
            ],
            $result
        );

        $_SESSION['survey_' . self::$surveyId]['maxstep'] = 2;
        $_SESSION['survey_' . self::$surveyId]['step'] = 1;

        $moveResult = \LimeExpressionManager::NavigateForwards();

        // Check result from qanda.
        $qanda = \retrieveAnswers(
            $_SESSION['survey_' . self::$surveyId]['fieldarray'][0],
            self::$surveyId
        );

        // NB: Empty value, since default answer expression is not parsed by qanda.
        $this->assertNotEquals(false, (strpos($qanda[0][1], "val('')")));

        // NB: Value below is todays date in format Y-m-d, which can't be
        // parsed by qanda (expects Y-m-d H:i).
        // $_SESSION['survey_' . self::$surveyId][$sgqa]);
    }

}
