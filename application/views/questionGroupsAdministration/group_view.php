<?php
/**
 * @var QuestionGroupsAdministrationController $this
 * @var Survey $oSurvey
 */

?>
<div class='side-body'>
    <div class="pagetitle h1"><?php eT('Group summary'); ?></div>
    <div id="groupdetails" class="summary-table">
        <div class="row">
            <div class="col-2"><strong>
                    <?php
                    eT("Title"); ?>:</strong></div>
            <div class="col-10">
                <strong>
                <?= $grow['group_name']; ?> (<?= $grow['gid']; ?>)
                </strong>
            </div>
        </div>
        <div class="row">
            <div class="col-2"><strong>
                    <?php eT("Description:"); ?></strong>
            </div>
            <div class="col-10">
                <?php
                if (trim((string) $grow['description']) != '') {
                    templatereplace($grow['description']);
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                }
                ?>
            </div>
        </div>
        <?php
        if (trim((string) $grow['grelevance']) != '') { ?>
            <div class="row">
                <div class="col-2">
                    <strong><?php eT("Condition:"); ?></strong>
                </div>
                <div class="col-10">
                    <?php
                    LimeExpressionManager::ProcessString('{' . $grow['grelevance'] . '}');
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </div>
            </div>
        <?php
        } ?>
        <?php
        if (trim((string) $grow['randomization_group']) != '') {
            ?>
            <div class="row">
                <div class="col-2"><?php eT("Randomization group:"); ?></div>
                <div class="col-10"><?php echo $grow['randomization_group']; ?></div>
            </div>
            <?php
        }
        // TMSW Condition->Relevance:  Use relevance equation or different EM query to show dependencies
        if (!is_null($condarray)) { ?>
            <div class="row">
                <div class="col-2">
                    <strong><?php eT("Questions with conditions to this group"); ?>:</strong>
                </div>
                <div class="col-10">
                    <?php
                    foreach ($condarray[$gid] as $depgid => $deprow) {
                        foreach ($deprow['conditions'] as $depqid => $depcid) {
                            $listcid = implode("-", $depcid); ?>
                            <a href='<?php echo $this->createUrl("admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$depgid/qid/$depqid", array('markcid' => implode("-", $depcid))); ?>'>
                                [QID: <?= $depqid; ?>]
                            </a>
                        <?php
                        }
                    } ?>
                </div>
            </div>
        <?php
        } ?>
    </div>
</div>

<?php
App()->getClientScript()->registerScript(
    'activatePanelClickable',
    'LS.pageLoadActions.panelClickable()',
    LSYii_ClientScript::POS_POSTSCRIPT
);

// Reset topbar to "non-extended" mode.
// If this view wasn't loaded by ajax (ex: from the side menu) this wouldn't be necessary
Yii::app()->getClientScript()->registerScript(
    "ViewGroup_topbar_switch",
    'window.EventBus.$emit("doFadeEvent", false);',
    LSYii_ClientScript::POS_END
);
?>
