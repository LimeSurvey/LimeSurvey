<?php if ($oQuestion->qid !== 0): ?>
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
                    <span class="fa fa-eye"></span>
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
                <span class="fa fa-eye"></span>
                <?php eT("Preview question group");?>
            </a>
        <?php endif; ?>
        <?php if (count($surveyLanguages) > 1): ?>

        <!-- Preview question multilanguage -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-eye"></span>
            <?php eT("Preview question"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/{$languageCode}"); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else:?>

        <!-- Preview question single language -->
        <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button" target="_blank">
            <span class="fa fa-eye"></span>
            <?php eT("Preview question");?>
        </a>
        <?php endif; ?>
    <?php endif; ?>
<?php else: ?>
    <!-- Import -->
    <?php if($hasSurveyContentCreatePermission):?>
        <?php if($oSurvey->active!='Y'): ?>
            <a class="btn btn-default" id="import-button" href="<?php echo Yii::App()->createUrl("questionAdministration/importView", ["surveyid" => $surveyid, "groupid" => $gid]); ?>" role="button">
                <span class="icon-import icon"></span>
                <?php eT("Import question"); ?>
            </a>
        <?php else: ?>
            <button class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can not import questions because the survey is currently active."); ?>">
                <span class="icon-import icon"></span>
                <?php eT("Import question"); ?>
            </button>
        <?php endif; ?>
    <?php endif;?>
<?php endif; ?>

