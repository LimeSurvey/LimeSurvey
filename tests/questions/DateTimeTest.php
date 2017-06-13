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
     * 
     */
    public static function setupBeforeClass()
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.replacements_helper', true);
        \Yii::import('application.helpers.surveytranslator_helper', true);
        \Yii::import('application.helpers.admin.import_helper', true);
        \Yii::import('application.helpers.expressions.em_manager_helper', true);

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
     */
    public static function teardownAfterClass()
    {
        // TODO: Delete questions and groups.
        $result1 = \Survey::model()->deleteAll('sid = :sid', array('sid' => self::$surveyId));
        $result2 = \SurveyLanguageSetting::model()->deleteAll('surveyls_survey_id = :sid', array('sid' => self::$surveyId));
        if (!$result1 || !$result2) {
            die('Fatal error: Could not cleanup after tests: Could not delete imported survey');
        }
    }

    /**
     * 
     */
    public function testBasic()
    {
        $em = \LimeExpressionManager::singleton();

        $question = \Question::model()->find(
            'title = :title AND sid = :sid',
            [
                'title' => 'q2',
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
        $em->setCurrentQset($qset);

        $surveyId = 975622;
        $LEMdebugLevel = 0;
        $thissurvey = \getSurveyInfo($surveyId);
        $radix = \getRadixPointData($thissurvey['surveyls_numberformat']);
        $radix = $radix['separator'];
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

        $surveyMode = 'group';
        \LimeExpressionManager::StartSurvey($surveyId, $surveyMode, $surveyOptions, false, $LEMdebugLevel);

        $qid = $question->qid;
        $gseq = 0;
        $_POST['relevance' . $qid] = 1;
        $_POST['relevanceG' . $gseq] = 1;
        $_POST[$sgqa] = 'asd';

        $result = \LimeExpressionManager::ProcessCurrentResponses();
        var_dump($result);

        $originalPrefix = \Yii::app()->user->getStateKeyPrefix();
        \Yii::app()->user->setStateKeyPrefix('frontend' . $surveyId);
        $flashes = \Yii::app()->user->getFlashes();
        var_dump($flashes);
        \Yii::app()->user->setStateKeyPrefix($originalPrefix);
    }
}
