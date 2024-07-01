<?php if($hasSurveyContentReadPermission): ?>
    <!-- Check survey logic -->
    <li>
        <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>">
            <span class="ri-checkbox-fill"></span>
            <?php eT("Check logic"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <li>
        <a class="dropdown-item" href="<?php echo Yii::App()->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>">
            <span class="ri-download-fill"></span>
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
                <a class="dropdown-item" href="#" onclick="return false;"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmation-modal"
                    data-onclick='(function() { <?php echo convertGETtoPOST(
                        Yii::app()->createUrl(
                            "questionGroupsAdministration/delete/",
                            [
                                "asJson" => true,
                                "surveyid" => $surveyid,
                                "gid" => $gid,
                                "landOnSideMenuTab" => 'structure'
                            ]
                        )
                    ); ?> })'
                    data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>"
                >
                    <span class="ri-delete-bin-fill text-danger"></span>
                    <?php eT("Delete group"); ?>
                </a>
            </li>
        <?php else: ?>
            <?php // there is at least one question having a condition on its content ?>
            <li class="disabled">
                <a class="btntooltip dropdown-item" disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>" >
                    <span class="ri-delete-bin-fill text-danger"></span>
                    <?php eT("Delete group"); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php else:?>
        <!-- Activated -->
        <li class="disabled">
            <a class="btntooltip dropdown-item" disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("It is not possible to add/delete groups if the survey is active."); ?>" >
                <span class="ri-delete-bin-fill text-danger"></span>
                <?php eT("Delete group"); ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
