<?php

namespace ls\tests;

class TestHelper extends \PHPUnit_Framework_TestCase {

    /**
     * @param string $title
     * @param int $surveyId
     * @return array
     */
    public function getSgqa($title, $surveyId)
    {
        $question = \Question::model()->find(
            'title = :title AND sid = :sid',
            [
                'title' => $title,
                'sid'   => $surveyId
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
            $surveyId,
            $group->gid,
            $question->qid
        );

        return [$question, $group, $sgqa];
    }

    /**
     * Get survey options for imported survey.
     * @param int $surveyId
     * @return array
     */
    public function getSurveyOptions($surveyId)
    {
        $thissurvey = \getSurveyInfo($surveyId);
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
            'hyperlinkSyntaxHighlighting' => (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY),
            'ipaddr' => ($thissurvey['ipaddr'] == 'Y'),
            'radix'=>$radix,
            'refurl' => (($thissurvey['refurl'] == "Y" && isset($_SESSION[$LEMsessid]['refurl'])) ? $_SESSION[$LEMsessid]['refurl'] : null),
            'savetimings' => ($thissurvey['savetimings'] == "Y"),
            'surveyls_dateformat' => (isset($thissurvey['surveyls_dateformat']) ? $thissurvey['surveyls_dateformat'] : 1),
            'startlanguage'=>(isset(App()->language) ? App()->language : $thissurvey['language']),
            'target' => \Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$thissurvey['sid'].DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR,
            'tempdir' => \Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR,
            'timeadjust' => (isset($timeadjust) ? $timeadjust : 0),
            'token' => (isset($clienttoken) ? $clienttoken : null),
        );
        return $surveyOptions;
    }

    /**
     * @param int $surveyId
     * @return void
     */
    public function activateSurvey($surveyId)
    {
        $survey = \Survey::model()->findByPk($surveyId);
        $survey->anonymized = '';
        $survey->datestamp = '';
        $survey->ipaddr = '';
        $survey->refurl = '';
        $survey->savetimings = '';
        $survey->save();
        \Survey::model()->resetCache();  // Make sure the saved values will be picked up

        $result = \activateSurvey($surveyId);
        $this->assertEquals(['status' => 'OK'], $result, 'Activate survey is OK');
    }

    /**
     * @param int $surveyId
     * @return void
     */
    public function deactivateSurvey($surveyId)
    {
        $date     = date('YmdHis');
        $oldSurveyTableName = \Yii::app()->db->tablePrefix."survey_{$surveyId}";
        $newSurveyTableName = \Yii::app()->db->tablePrefix."old_survey_{$surveyId}_{$date}";
        \Yii::app()->db->createCommand()->renameTable($oldSurveyTableName, $newSurveyTableName);
        $survey = \Survey::model()->findByPk($surveyId);
        $survey->active = 'N';
        $result = $survey->save();
        $this->assertTrue($result);
    }
}
