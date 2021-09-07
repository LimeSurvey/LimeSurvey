<?php if($hasSurveyContentReadPermission): ?>
    <!-- Check survey logic -->
    <li>
        <a class="pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>">
            <span class="icon-expressionmanagercheck"></span>
            <?php eT("Check logic"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <li>
        <a href="<?php echo Yii::App()->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>">
            <span class="icon-export"></span>
            <?php eT("Export"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentDeletePermission):?>
    <!-- Delete -->
    <?php if( $oSurvey->active != "Y" ):?>
        <?php if(is_null($condarray)):?>
            <?php // can delete group and question ?>
            <li>
                <a href="#" onclick="return false;"
                    data-toggle="modal"
                    data-target="#confirmation-modal"
                    data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionGroupsAdministration/delete/", ["asJson" => true, "surveyid" => $surveyid, "gid"=>$gid])); ?> })'
                    data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>"
                >
                    <span class="fa fa-trash text-danger"></span>
                    <?php eT("Delete group"); ?>
                </a>
            </li>
        <?php else: ?>
            <?php // there is at least one question having a condition on its content ?>
            <li class="disabled">
                <a class="btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>" >
                    <span class="fa fa-trash text-danger"></span>
                    <?php eT("Delete group"); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php else:?>
        <!-- Activated -->
        <li class="disabled">
            <a class="btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("It is not possible to add or delete a group on an active survey."); ?>" >
                <span class="fa fa-trash text-danger"></span>
                <?php eT("Delete"); ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>