<?php
/**
 * @var QuestionGroupsAdministrationController $this
 * @var Survey $oSurvey
 */

?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3 class="pagetitle h3"><?php eT('Group summary'); ?></h3>
    <div class="row">
        <div class="col-12 content-right">

            <table id='groupdetails'>
                <tr>
                    <td><strong>
                            <?php eT("Title"); ?>:</strong></td>
                    <td>
                        <?php echo $grow['group_name']; ?> (<?php echo $grow['gid']; ?>)
                    </td>
                </tr>
                <tr>
                    <td><strong>
                            <?php eT("Description:"); ?></strong>&nbsp;&nbsp;&nbsp;
                    </td>
                    <td>
                        <?php if (trim($grow['description']) != '') {
                            templatereplace($grow['description']);
                            echo LimeExpressionManager::GetLastPrettyPrintExpression();
                        } ?>
                    </td>
                </tr>
                <?php if (trim($grow['grelevance']) != '') { ?>
                    <tr>
                        <td><strong>
                                <?php eT("Condition:"); ?></strong>
                        </td>
                        <td>
                            <?php
                            LimeExpressionManager::ProcessString('{' . $grow['grelevance'] . '}');
                            echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                            ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php
                if (trim($grow['randomization_group']) != '') {
                    ?>
                    <tr>
                        <td><?php eT("Randomization group:"); ?></td>
                        <td><?php echo $grow['randomization_group']; ?></td>
                    </tr>
                    <?php
                }
                // TMSW Condition->Relevance:  Use relevance equation or different EM query to show dependencies
                if (!is_null($condarray)) { ?>
                    <tr>
                        <td><strong>
                                <?php eT("Questions with conditions to this group"); ?>:</strong></td>
                        <td>
                            <?php foreach ($condarray[$gid] as $depgid => $deprow) {
                                foreach ($deprow['conditions'] as $depqid => $depcid) {
                                    $listcid = implode("-", $depcid); ?>
                                    <a href='<?php echo $this->createUrl("admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$depgid/qid/$depqid",
                                        array('markcid' => implode("-", $depcid))
                                    ); ?>'>[QID: <?php echo $depqid; ?>]</a>
                                <?php }
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')): ?>
        <div id="survey-action-title" class="pagetitle h3"><?php eT('Group quick actions'); ?></div>
        <div class="row welcome survey-action">
            <div class="col-12 content-right">

                <!-- create question in this group -->
                <div class="col-xl-3">
                    <div class="card card-primary text-center <?php if ($oSurvey->isActive) {
                        echo 'disabled';
                    } else {
                        echo 'card-clickable';
                    } ?>" id="panel-1" data-url="<?php echo $this->createUrl('questionAdministration/create', array('surveyid' => $surveyid, 'gid' => $gid)); ?>">
                        <div class="card-header bg-primary">
                            <div class=""><?php eT("Add new question to group"); ?></div>
                        </div>
                        <div class="card-body">
                            <span class="icon-add text-success" style="font-size: 3em;"></span>
                            <p class='btn-link'>
                                <?php eT("Add new question to group"); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
