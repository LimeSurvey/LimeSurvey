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
    private const PREFIX_SETTINGS = ['subquestionprefix', 'answeroptionprefix'];

    public function testSampleSubquestionsFollowPrefixWithoutLetterFallback()
    {
        $db = \Yii::app()->db;
        $user = \Yii::app()->user;
        $originalUserId = $user->getState('__id');
        $originalLoginId = \Yii::app()->session['loginID'];
        $originalSettings = $db->createCommand()
            ->select('*')
            ->from('{{settings_user}}')
            ->where(['and', 'uid=1', ['in', 'stg_name', self::PREFIX_SETTINGS]])
            ->queryAll();
        $parentQid = null;
        $existingChildQids = [];

        try {
            $user->setId(1);
            \SettingsUser::model()->deleteAllByAttributes(['uid' => 1, 'stg_name' => self::PREFIX_SETTINGS]);
            self::importSurvey(
                \Yii::app()->getConfig('rootdir')
                . '/tests/data/surveys/limesurvey_survey_497740_array_filter_list_ranking_server_check.lss'
            );
            $sid = (int) self::$surveyId;
            $gid = (int) $db->createCommand('select min(gid) from {{groups}} where sid=' . $sid)->queryScalar();
            $parentQid = (int) $db->createCommand(
                'select min(qid) from {{questions}} where parent_qid=0 and sid=' . $sid
            )->queryScalar();
            $existingChildQids = $this->childQids($parentQid);

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
                $m->invoke($ctl, 1, $sid, $gid, $parentQid, 'en', 'x');
                $m->invoke($ctl, 2, $sid, $gid, $parentQid, 'en', 'x');
                $created = array_values(array_diff($this->childQids($parentQid), $existingChildQids));
                $out[json_encode($pref)] = $created === [] ? [] : $db->createCommand()
                    ->select('title')
                    ->from('{{questions}}')
                    ->where(['in', 'qid', $created])
                    ->order('qid')
                    ->queryColumn();
                $this->deleteQuestions($created);
            }
            $this->assertSame(['1', '2'], $out['null']);
            $this->assertSame(['1', '2'], $out['""']);
            $this->assertSame(['SQ001', 'SQ002'], $out['"SQ"']);
            $this->assertSame(['AB001', 'AB002'], $out['"AB"']);
        } finally {
            if ($parentQid) {
                $this->deleteQuestions(array_diff($this->childQids($parentQid), $existingChildQids));
            }
            $db->createCommand()->delete(
                '{{settings_user}}',
                ['and', 'uid=1', ['in', 'stg_name', self::PREFIX_SETTINGS]]
            );
            foreach ($originalSettings as $row) {
                $db->createCommand()->insert('{{settings_user}}', $row);
            }
            $user->setId($originalUserId);
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }

    /**
     * @return int[]
     */
    private function childQids(int $parentQid): array
    {
        return array_map('intval', \Yii::app()->db->createCommand()
            ->select('qid')
            ->from('{{questions}}')
            ->where('parent_qid=:p', [':p' => $parentQid])
            ->queryColumn());
    }

    /**
     * @param int[] $qids
     */
    private function deleteQuestions(array $qids): void
    {
        if ($qids === []) {
            return;
        }
        $db = \Yii::app()->db;
        $db->createCommand()->delete('{{question_l10ns}}', ['in', 'qid', array_values($qids)]);
        $db->createCommand()->delete('{{questions}}', ['in', 'qid', array_values($qids)]);
    }
}
