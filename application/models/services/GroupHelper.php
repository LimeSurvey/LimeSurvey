<?php

namespace LimeSurvey\Models\Services;

class GroupHelper
{
    /**
     * Reorder groups and questions
     *
     * REFACTORED in SurveyAdministration
     *
     * @param int $iSurveyID Given Survey ID
     *
     * @return void
     */
    public function reorderGroup($iSurveyID, $sOrgdata)
    {
        $grouporder = 1;
        $orgdata = $this->getOrgdata($sOrgdata);
        foreach ($orgdata as $ID => $parent) {
            if ($parent == 'root' && $ID[0] == 'g') {
                \QuestionGroup::model()->updateAll(
                    array('group_order' => $grouporder),
                    'gid=:gid',
                    array(':gid' => (int) substr($ID, 1))
                );
                $grouporder++;
            } elseif ($ID[0] == 'q') {
                $qid = (int) substr($ID, 1);
                $gid = (int) substr((string) $parent, 1);
                if (!isset($aQuestionOrder[$gid])) {
                    $aQuestionOrder[$gid] = 0;
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
                    App()->setFlashMessage(sprintf(gT("Unable to reorder question %s."), $oQuestion->title), 'warning');
                }
            }
        }
        \LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        App()->setFlashMessage(gT("The new question group/question order was successfully saved."));
    }

    /**
     * Get the new question organization from the post data.
     * This function replaces parse_str, since parse_str
     * is bound by max_input_vars.
     *
     * @return array
     */
    private function getOrgdata($orgdata)
    {
        $ex = explode('&', $orgdata);
        $vars = array();
        foreach ($ex as $str) {
            list($list, $target) = explode('=', $str);
            $list = str_replace('list[', '', $list);
            $list = str_replace(']', '', $list);
            $vars[$list] = $target;
        }

        return $vars;
    }
}
