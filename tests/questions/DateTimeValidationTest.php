<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-10-27
 * @group datevalidation
 */
class DateTimeValidationTest extends TestBaseClass
{
    /**
     * @var int
     */
    public static $surveyId = null;

    /**
     * Import survey in tests/surveys/.
     */
    public static function setupBeforeClass()
    {
        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_834477.lss';
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
     * "currentQset" in EM.
     */
    protected function getQuestionSetForQ2(\Question $question, \QuestionGroup $group, $sgqa)
    {
        $qset = array($question->qid => array
            (
                'info' => array
                (
                    'relevance' => '1',
                    'grelevance' => '',
                    'qid' => $question->qid,
                    'qseq' => 1,
                    'gseq' => 0,
                    'jsResultVar_on' => 'answer' . $sgqa,
                    'jsResultVar' => 'java' . $sgqa,
                    'type' => $question->type,
                    'hidden' => false,
                    'gid' => $group->gid,
                    'mandatory' => $question->mandatory,
                    'eqn' => '',
                    'help' => '',
                    'qtext' => '',
                    'code' => $question->title,
                    'other' => 'N',
                    'default' => null,
                    'rootVarName' => $question->title,
                    'rowdivid' => '',
                    'aid' => '',
                    'sqid' => '',
                ),
                'relevant' => true,
                'hidden' => false,
                'relEqn' => '',
                'sgqa' => $sgqa,
                'unansweredSQs' => $sgqa,
                'valid' => true,
                'validEqn' => '',
                'prettyValidEqn' => '',
                'validTip' => '',
                'prettyValidTip' => '',
                'validJS' => '',
                'invalidSQs' => '',
                'relevantSQs' => $sgqa,
                'irrelevantSQs' => '',
                'subQrelEqn' => '',
                'mandViolation' => false,
                'anyUnanswered' => true,
                'mandTip' => '',
                'message' => '',
                'updatedValues' => array(),
                'sumEqn' => '',
                'sumRemainingEqn' => ''
            )
        );
        return $qset;
    }

    /**
     * 
     */
    public function testBasic()
    {
        /*
        \Yii::app()->setController(new DummyController('dummyid'));
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('G1Q00005', self::$surveyId);

        $qset = $this->getQuestionSetForQ2($question, $group, $sgqa);

        $em = \LimeExpressionManager::singleton();
        $em->setCurrentQset($qset);

        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            $surveyMode,
            $surveyOptions,
            false,
            $LEMdebugLevel
        );

        $qid = $question->qid;
        $gseq = 0;
        $_POST['relevance' . $qid] = 1;
        $_POST['relevanceG' . $gseq] = 1;
        $_POST[$sgqa] = '27/10/2017';

        //$moveResult = \LimeExpressionManager::NavigateForwards();
        $result = \LimeExpressionManager::ProcessCurrentResponses();
        echo '<pre>'; var_dump($_SESSION); echo '</pre>';
         */

        $thissurvey = getSurveyInfo(self::$surveyId);

        $runtime = new \SurveyRuntimeHelper();
        $runtime->run(
            self::$surveyId,
            [
                'surveyid'   => self::$surveyId,
                'thissurvey' => $thissurvey,
                'param'      => []
            ]
        );
    }
}
