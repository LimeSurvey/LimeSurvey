<!-- test/execute survey -->
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