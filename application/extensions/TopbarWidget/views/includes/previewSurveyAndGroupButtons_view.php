<!-- test/execute survey -->
<?php if (count($surveyLanguages) > 1): ?>
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php if($oSurvey->active=='N'):?>
                <span class="fa fa-eye" ></span>
                <?php eT('Preview survey');?>
            <?php else: ?>
                <span class="fa fa-play" ></span>
                <?php eT('Run survey');?>
            <?php endif;?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" style="min-width : 252px;">
            <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                <li>
                    <a target='_blank' href='<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$languageCode));?>'>
                        <?php echo $languageName; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <a class="btn btn-default  btntooltip" href="<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank'>
        <?php if($oSurvey->active=='N'):?>
            <span class="fa fa-eye" ></span>
            <?php eT('Preview survey');?>
        <?php else: ?>
            <span class="fa fa-play" ></span>
            <?php eT('Run survey');?>
        <?php endif;?>
    </a>
<?php endif;?>

<?php if($hasSurveyContentUpdatePermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Preview group multilanguage -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-eye" ></span>
                <?php eT("Preview question group"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $languageCode); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else:?>
        <!-- Preview group single language -->
        <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" role="button" target="_blank">
            <span class="fa fa-eye" ></span>
            <?php eT("Preview question group");?>
        </a>
    <?php endif; ?>
<?php endif; ?>