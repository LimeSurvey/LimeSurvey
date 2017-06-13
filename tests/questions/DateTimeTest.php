<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-13
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
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
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.replacements_helper', true);
        \Yii::import('application.helpers.surveytranslator_helper', true);
        \Yii::import('application.helpers.admin.import_helper', true);
        \Yii::import('application.helpers.expressions.em_manager_helper', true);

        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_975622.lss';
        if (!file_exists($surveyFile)) {
            die('Fatal: found no survey file');
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
            // TODO: Login programmatically instead.
            $query = 'UPDATE lime_surveys SET owner_id = 1 WHERE sid = :sid';
            $command = \Yii::app()->db->createCommand($query);
            $command->execute([
                'sid' => self::$surveyId
            ]);
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
     * @param string $title
     * @return array
     */
    protected function getSgqa($title)
    {
        $question = \Question::model()->find(
            'title = :title AND sid = :sid',
            [
                'title' => $title,
                'sid'   => self::$surveyId
            ]
        );

        $this->assertNotEmpty($question);

        $group = \QuestionGroup::model()->find(
            'gid = :gid',
            [
                'gid' => $question->gid
            ]
        );

        $this->assertNotEmpty($group);

        $sgqa = sprintf(
            '%sX%sX%s',
            self::$surveyId,
            $group->gid,
            $question->qid
        );

        return [$question, $group, $sgqa];
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
     * Get survey options for imported survey.
     * @return array
     */
    protected function getSurveyOptions()
    {
        $thissurvey = \getSurveyInfo(self::$surveyId);
        $radix = \getRadixPointData($thissurvey['surveyls_numberformat']);
        $radix = $radix['separator'];
        $LEMdebugLevel = 0;
        $surveyOptions = array(
            'active' => ($thissurvey['active'] == 'Y'),
            'allowsave' => ($thissurvey['allowsave'] == 'Y'),
            'anonymized' => ($thissurvey['anonymized'] != 'N'),
            'assessments' => ($thissurvey['assessments'] == 'Y'),
            'datestamp' => ($thissurvey['datestamp'] == 'Y'),
            'deletenonvalues'=>\Yii::app()->getConfig('deletenonvalues'),
            'hyperlinkSyntaxHighlighting' => (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY), // TODO set this to true if in admin mode but not if running a survey
            'ipaddr' => ($thissurvey['ipaddr'] == 'Y'),
            'radix'=>$radix,
            'refurl' => (($thissurvey['refurl'] == "Y" && isset($_SESSION[$LEMsessid]['refurl'])) ? $_SESSION[$LEMsessid]['refurl'] : NULL),
            'savetimings' => ($thissurvey['savetimings'] == "Y"),
            'surveyls_dateformat' => (isset($thissurvey['surveyls_dateformat']) ? $thissurvey['surveyls_dateformat'] : 1),
            'startlanguage'=>(isset(App()->language) ? App()->language : $thissurvey['language']),
            'target' => \Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$thissurvey['sid'].DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR,
            'tempdir' => \Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR,
            'timeadjust' => (isset($timeadjust) ? $timeadjust : 0),
            'token' => (isset($clienttoken) ? $clienttoken : NULL),
        );
        return $surveyOptions;
    }

    /**
     * Test wrong date input and error message.
     */
    public function testWrongInput()
    {
        list($question, $group, $sgqa) = $this->getSgqa('q2');

        $qset = $this->getQuestionSetForQ2($question, $group, $sgqa);

        $em = \LimeExpressionManager::singleton();
        $em->setCurrentQset($qset);

        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $surveyOptions = $this->getSurveyOptions();
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
        $this->assertEquals('asd', $result[$sgqa]['value']);

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
        list($question, $group, $sgqa) = $this->getSgqa('q2');

        $qset = $this->getQuestionSetForQ2($question, $group, $sgqa);

        $em = \LimeExpressionManager::singleton();
        $em->setCurrentQset($qset);

        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $surveyOptions = $this->getSurveyOptions();
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
}
