<!-- test/execute survey -->
<?php if (count($surveyLanguages) > 1): ?>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php if($oSurvey->active=='N'):?>
                <span class="ri-eye-fill" ></span>
                <?php eT('Preview survey');?>
            <?php else: ?>
                <span class="ri-play-fill" ></span>
                <?php eT('Run survey');?>
            <?php endif;?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" style="min-width : 252px;">
            <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                <li>
                    <a class="dropdown-item" target='_blank' href='<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$languageCode));?>'>
                        <?php echo $languageName; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <a class="btn btn-outline-secondary btntooltip" href="<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank' role="button">
        <?php if($oSurvey->active=='N'):?>
            <span class="ri-eye-fill" ></span>
            <?php eT('Preview survey');?>
        <?php else: ?>
            <span class="ri-play-fill" ></span>
            <?php eT('Run survey');?>
        <?php endif;?>
    </a>
<?php endif;?>

<?php if($hasSurveyContentUpdatePermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Preview group multilanguage -->
        <div class="btn-group">
            <button type="button" role="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="ri-eye-fill" ></span>
                <?php eT("Preview question group"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $languageCode); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <!-- Preview group single language -->
        <a type="button" class="btn btn-outline-secondary"
           href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>"
           target="_blank">
            <span class="ri-eye-fill"></span>
            <?php eT("Preview question group"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>
