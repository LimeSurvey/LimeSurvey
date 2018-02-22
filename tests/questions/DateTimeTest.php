<?php

namespace ls\tests;


/**
 * @since 2017-06-13
 * @group date
 */
class DateTimeTest extends TestBaseClass
{

    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder.'/limesurvey_survey_975622.lss';
        self::importSurvey($surveyFile);

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
                    'type' => 'D',
                    'hidden' => false,
                    'gid' => $group->gid,
                    'mandatory' => 'N',
                    'eqn' => '',
                    'help' => '',
                    'qtext' => '',
                    'code' => 'q2',
                    'other' => 'N',
                    'default' => null,
                    'rootVarName' => 'q2',
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
     * Test wrong date input and error message.
     * @group datewronginput
     */
    public function testWrongInput()
    {
        $contr = new DummyController('dummyid');
        \Yii::app()->setController($contr);

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('q2', self::$surveyId);

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
        $_POST[$sgqa] = 'asd';

        $result = \LimeExpressionManager::ProcessCurrentResponses();
        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result), 'One question from ProcessCurrentResponses');
        $this->assertEquals('INVALID', $result[$sgqa]['value']);

        $originalPrefix = \Yii::app()->user->getStateKeyPrefix();
        \Yii::app()->user->setStateKeyPrefix('frontend' . self::$surveyId);
        $flashes = \Yii::app()->user->getFlashes();

        $this->assertNotEmpty($flashes);
        $this->assertEquals(1, count($flashes), 'One error message');

        \Yii::app()->user->setStateKeyPrefix($originalPrefix);
    }

    /**
     * Test correct date.
     */
    public function testCorrectDateFormat()
    {
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('q2', self::$surveyId);

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
        $_POST[$sgqa] = '23/12/2016';

        $result = \LimeExpressionManager::ProcessCurrentResponses();
        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result), 'One question from ProcessCurrentResponses');
        $this->assertEquals('2016-12-23 00:00', $result[$sgqa]['value']);

        $originalPrefix = \Yii::app()->user->getStateKeyPrefix();
        \Yii::app()->user->setStateKeyPrefix('frontend' . self::$surveyId);
        $flashes = \Yii::app()->user->getFlashes();

        $this->assertEmpty($flashes, 'No error message');

        \Yii::app()->user->setStateKeyPrefix($originalPrefix);
    }

    /**
     * q1 is hidden question with default answer "now".
     */
    public function testQ1()
    {
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('q1', self::$surveyId);
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
        //$_POST['relevance' . $qid] = 1;
        //$_POST['relevanceG' . $gseq] = 1;
        $moveResult = \LimeExpressionManager::NavigateForwards();
        $result = \LimeExpressionManager::ProcessCurrentResponses();
        $moveResult = \LimeExpressionManager::NavigateForwards();
        $result = \LimeExpressionManager::ProcessCurrentResponses();
        $this->assertEquals(date('Y-m-d'), $_SESSION['survey_' . self::$surveyId][$sgqa]);
    }
}
