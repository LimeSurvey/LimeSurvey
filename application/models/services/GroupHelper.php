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
     * @param int $iSurveyID Given Survey ID
     * @param array $orgdata Data to change
     *
     */
    public function reorderGroup($iSurveyID, $orgdata)
    {
        $result = array();
        $grouporder = 1;

        foreach ($orgdata as $ID => $parent) {
            if ($parent == 'root' && substr_compare($ID, 'g', 0, 1) === 0) {
                \QuestionGroup::model()->updateAll(
                    array('group_order' => $grouporder),
                    'gid=:gid',
                    array(':gid' => (int) substr($ID, 1))
                );
                $grouporder++;
            } elseif (substr_compare($ID, 'q', 0, 1) === 0) {
                $qid = (int) substr($ID, 1);
                $gid = (int) substr((string) $parent, 1);
                if (!isset($aQuestionOrder[$gid])) {
                    $aQuestionOrder[$gid] = 1;
                }

                $oQuestion = \Question::model()->findByPk($qid);
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
        \LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

        if (!empty($result)) {
            return $result;
        }

        $result['type'] = 'success';
        return $result;
    }
}
