<?php

namespace LimeSurvey\Models\Services;

class GroupHelper
{
    /**
     * Reorder groups and questions
     *
     * REFACTORED in SurveyAdministration
     * @TODO Reordering should be handled by existing function in new QuestionGroupService class
     *
     * Note: Only groups and questions of the given survey are changed, and questions can only be moved to its groups.
     *
     * @param int $iSurveyID Given Survey ID
     * @param array $orgdata Data to change
     * @return array Result with 'type' ('success' or 'error') and, on error, the 'question-titles' that could not be saved
     */
    public function reorderGroup($iSurveyID, $orgdata)
    {
        $result = array();
        $grouporder = 1;
        $surveyGroupIds = array_map('intval', \Yii::app()->db->createCommand()
            ->select('gid')
            ->from('{{groups}}')
            ->where('sid = :sid', [':sid' => (int) $iSurveyID])
            ->queryColumn());

        foreach ($orgdata as $ID => $parent) {
            if ($parent == 'root' && substr_compare($ID, 'g', 0, 1) === 0) {
                \QuestionGroup::model()->updateAll(
                    array('group_order' => $grouporder),
                    'gid=:gid AND sid=:sid',
                    array(':gid' => (int) substr($ID, 1), ':sid' => (int) $iSurveyID)
                );
                $grouporder++;
            } elseif (substr_compare($ID, 'q', 0, 1) === 0) {
                $qid = (int) substr($ID, 1);
                $gid = (int) substr((string) $parent, 1);
                if (!in_array($gid, $surveyGroupIds, true)) {
                    continue;
                }
                if (!isset($aQuestionOrder[$gid])) {
                    $aQuestionOrder[$gid] = 1;
                }

                $oQuestion = \Question::model()->findByAttributes(['qid' => $qid, 'sid' => (int) $iSurveyID]);
                if (empty($oQuestion)) {
                    continue;
                }
                /* @var integer old value of gid to check if updated */
                $oldGid = $oQuestion->gid;
                /* Update quuestion, and update other if saved */
                $oQuestion->gid = $gid;
                $oQuestion->question_order = $aQuestionOrder[$gid];
                if ($oQuestion->save(true)) {
                    if ($oldGid != $gid) {
                        fixMovedQuestionConditions($qid, $oldGid, $gid, $iSurveyID);
                    }
                    \Question::model()->updateAll(
                        array(
                            'question_order' => $aQuestionOrder[$gid],
                            'gid' => $gid
                        ),
                        'qid=:qid',
                        array(':qid' => $qid)
                    );
                    \Question::model()->updateAll(array('gid' => $gid), 'parent_qid=:parent_qid', array(':parent_qid' => $qid));
                    $aQuestionOrder[$gid]++;
                } else {
                    $result['type'] = 'error';
                    $result['question-titles'][] = $oQuestion->title;
                }
            }
        }
        // Group reorders use updateAll(), which bypasses QuestionGroup::afterSave()
        (new SurveyDetailService())->touchSurveyLastModified((int) $iSurveyID);
        \LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

        if (!empty($result)) {
            return $result;
        }

        $result['type'] = 'success';
        return $result;
    }
}
