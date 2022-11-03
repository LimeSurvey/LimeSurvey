<?php if ($oQuestion->qid !== 0): ?>
    <!-- test/execute survey -->
    <?php if (count($surveyLanguages) > 1): ?>
        <div class="dropdown">
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
        <a class="btn btn-outline-secondary btntooltip" href="<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>" accesskey='d' target='_blank'>
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
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="ri-eye-fill"></span>
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
            <a class="btn btn-outline-secondary"
               href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>"
               target="_blank">
                <span class="ri-eye-fill"></span>
                <?php eT("Preview question group"); ?>
            </a>
        <?php endif; ?>
        <?php if (count($surveyLanguages) > 1): ?>

        <!-- Preview question multilanguage -->
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="ri-eye-fill"></span>
            <?php eT("Preview question"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/{$languageCode}"); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else:?>

        <!-- Preview question single language -->
        <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/$surveyid/gid/$gid/qid/$qid"); ?>" target="_blank">
            <span class="ri-eye-fill"></span>
            <?php eT("Preview question");?>
        </a>
        <?php endif; ?>
    <?php endif; ?>
<?php else: ?>
    <!-- Import -->
    <?php if($hasSurveyContentCreatePermission):?>
        <?php if($oSurvey->active!='Y'): ?>
            <a class="btn btn-outline-secondary" id="import-button" href="<?php echo Yii::App()->createUrl("questionAdministration/importView", ["surveyid" => $surveyid, "groupid" => $gid]); ?>" role="button">
                <span class="ri-upload-fill icon"></span>
                <?php eT("Import question"); ?>
            </a>
        <?php else: ?>
            <a role="button" class="btn btn-outline-secondary btntooltip" disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You can not import questions because the survey is currently active."); ?>">
                <span class="ri-upload-fill icon"></span>
                <?php eT("Import question"); ?>
            </a>
        <?php endif; ?>
    <?php endif;?>
<?php endif; ?>

