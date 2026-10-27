<?php
namespace ls\tests\unit\models;

use ls\tests\TestBaseClass;

/**
 * The sample question of a new survey and the empty-row helpers use the configured
 * subquestion prefix, and numeric codes when it is empty or not set (no SQ/AO fallback).
 * @group models
 */
class SampleQuestionCodePrefixTest extends TestBaseClass
{
    public function testSampleSubquestionsFollowPrefixWithoutLetterFallback()
    {
        \Yii::app()->user->setId(1);
        \SettingsUser::model()->deleteAllByAttributes(['uid' => 1, 'stg_name' => 'subquestionprefix']);
        \SettingsUser::model()->deleteAllByAttributes(['uid' => 1, 'stg_name' => 'answeroptionprefix']);
        self::importSurvey(\Yii::app()->getConfig('rootdir') . '/tests/data/surveys/limesurvey_survey_497740_array_filter_list_ranking_server_check.lss');
        $sid = (int) self::$surveyId;
        $db = \Yii::app()->db;
        $gid = (int) $db->createCommand('select min(gid) from {{groups}} where sid=' . $sid)->queryScalar();
        $qid = (int) $db->createCommand('select min(qid) from {{questions}} where parent_qid=0 and sid=' . $sid)->queryScalar();
        $q = new \Question();
        $q->sid = $sid;
        $this->assertSame('1', $q->getEmptySubquestion()->title);
        $m = new \ReflectionMethod(\SurveyAdministrationController::class, 'createSampleSubquestion');
        $m->setAccessible(true);
        $ctl = new \SurveyAdministrationController('surveyAdministration');
        $out = [];
        foreach ([null, '', 'SQ', 'AB'] as $pref) {
            if ($pref !== null) {
                \SettingsUser::setUserSetting('subquestionprefix', $pref, 1);
            }
            $max = (int) $db->createCommand('select max(qid) from {{questions}}')->queryScalar();
            $m->invoke($ctl, 1, $sid, $gid, $qid, 'en', 'x');
            $m->invoke($ctl, 2, $sid, $gid, $qid, 'en', 'x');
            $out[json_encode($pref)] = $db->createCommand('select title from {{questions}} where qid>' . $max . ' order by qid')->queryColumn();
            $db->createCommand('delete from {{questions}} where qid>' . $max)->execute();
        }
        $this->assertSame(['1', '2'], $out['null']);
        $this->assertSame(['1', '2'], $out['""']);
        $this->assertSame(['SQ001', 'SQ002'], $out['"SQ"']);
        $this->assertSame(['AB001', 'AB002'], $out['"AB"']);
    }
}
