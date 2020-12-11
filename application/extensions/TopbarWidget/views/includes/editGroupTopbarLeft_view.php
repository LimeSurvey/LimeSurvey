<?php if (count($oSurvey->allLanguages) > 1): ?>
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

            <span class="icon-do" ></span>
            <?php if($oSurvey->active=='N'):?>
                <?php eT('Preview survey');?>
            <?php else: ?>
                <?php eT('Execute survey');?>
            <?php endif;?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" style="min-width : 252px;">
            <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                <li>
                    <a target='_blank' href='<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                        <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <a class="btn btn-default  btntooltip" href="<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank'>
        <span class="icon-do" ></span>
        <?php if($oSurvey->active=='N'):?>
            <?php eT('Preview survey');?>
        <?php else: ?>
            <?php eT('Execute survey');?>
        <?php endif;?>
    </a>
<?php endif;?>

<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
    <?php if (count($oSurvey->allLanguages) > 1): ?>

        <!-- Preview multilangue -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="icon-do"></span>
            <?php eT("Preview current group"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                    <li>
                        <a target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>" >
                            <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else:?>

        <!-- Preview simple langue -->
        <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" role="button" target="_blank">
            <span class="icon-do"></span>
            <?php eT("Preview current group");?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Check survey logic -->
<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
    <a class="btn btn-default pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <span class="icon-expressionmanagercheck"></span>
        <?php eT("Check survey logic for current question group"); ?>
    </a>
<?php endif; ?>

<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')):?>

    <!-- Delete -->
    <?php if( $oSurvey->active != "Y" ):?>

        <!-- has question -->
        <?php if(is_null($condarray)):?>
            <!-- can delete group and question -->
            <button
                class="btn btn-default"
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("admin/questiongroups/sa/delete/", ["surveyid" => $surveyid, "gid"=>$gid])); ?> })'
                data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>"
                >
                <span class="fa fa-trash"></span>
                <?php eT("Delete current question group"); ?>
            </button>
        <?php else: ?>
            <!-- there is at least one question having a condition on its content -->
            <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>" >
                <span class="fa fa-trash"></span>
                <?php eT("Delete current question group"); ?>
            </a>
        <?php endif; ?>
    <?php else:?>
        <!-- Activated -->
        <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete this question group because the survey is currently active."); ?>" >
            <span class="fa fa-trash"></span>
            <?php eT("Delete current question group"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>

<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')):?>
    <!-- Export -->
    <a class="btn btn-default " href="<?php echo Yii::App()->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>" role="button">
        <span class="icon-export"></span>
        <?php eT("Export this question group"); ?>
    </a>
<?php endif; ?>