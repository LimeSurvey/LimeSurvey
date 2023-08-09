<?php

/**
 * @TODO check if this still used, this is deprcated and can be deleted
 * Subview of surveybar_view.
 * @param $oSurvey
 * @param $canactivate
 * @param $expired
 * @param $notstarted
 * @param $surveycontent
 * @param $icontext
 */

?>


<!-- survey activation -->
<?php if (!$oSurvey->isActive): ?>

    <!-- activate -->
    <?php if ($canactivate): ?>
        <button id='ls-activate-survey' class="btn btn-primary" href="<?php echo $this->createUrl("surveyAdministration/activate/surveyid/$oSurvey->sid"); ?>" type="button">
            <?php eT("Activate this survey"); ?>
        </button>

    <!-- can't activate -->
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveyactivation', 'update')): ?>
        <span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button id='ls-activate-survey' type="button" class="btn btn-primary btntooltip" disabled="disabled">
                <?php eT("Activate this survey"); ?>
            </button>
        </span>
    <?php endif; ?>
<?php else : ?>

    <!-- activate expired survey -->
    <?php if ($expired) : ?>
        <span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="<?php eT('This survey is active but expired.'); ?><br><?php eT('Click to adjust.'); ?>">
            <button href='<?php echo $this->createUrl("surveyAdministration/rendersidemenulink/subaction/publication", ['surveyid' => $oSurvey->sid]); ?>'class="btn btn-primary btntooltip" >
                <span class="ri-forbid-2-line">
                    &nbsp;
                </span>
                <?php eT("Expired"); ?>
            </button>
        </span>
    <?php elseif ($notstarted) : ?>
        <span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title='<?php eT("This survey is active but has a start date."); ?>'>
            <button type="button" class="btn btn-primary btntooltip" disabled="disabled" >
                <span class="ri-time-line">
                    &nbsp;
                </span>
                <?php eT("Activate this survey"); ?>
            </button>
        </span>
    <?php endif; ?>

    <!-- Stop survey -->
    <?php if ($canactivate): ?>
        <button
            class="btn btn-danger btntooltip"
            href="<?php echo $this->createUrl("surveyAdministration/deactivate/surveyid/$oSurvey->sid"); ?>"
            type="button">
            <i class="ri-stop-circle-fill" ></i>
            <?php eT("Stop this survey"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>


<!-- Preview/Run survey -->
<?php if ($oSurvey->isActive || $surveycontent) : ?>

    <!-- Multinlinguage -->
    <?php if (count($oSurvey->allLanguages) > 1): ?>
        <div class="btn-group">
          <button
            type="button" 
            class="btn btn-outline-secondary dropdown-toggle" 
            data-bs-toggle="dropdown" 
            aria-haspopup="true" 
            aria-expanded="false">
            <span class="ri-settings-5-fill" ></span>
            <?php echo $icontext; ?> <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" style="min-width : 252px;">
            <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                <li>
                    <a target='_blank' href='<?php echo $this->createUrl("survey/index", array('sid'=>$oSurvey->sid, 'newtest'=>"Y", 'lang'=>$tmp_lang)); ?>'>
                        <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                    </a>
                </li>
            <?php endforeach; ?>
          </ul>
        </div>

    <!-- uniq language -->
    <?php else: ?>
        <a class="btn btn-outline-secondary  btntooltip" href="<?php echo $this->createUrl("survey/index", array('sid'=>$oSurvey->sid, 'newtest'=>"Y", 'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank'>
            <span class="ri-settings-5-fill" ></span>
            <?php echo $icontext; ?>
        </a>
    <?php endif; ?>
<?php endif; ?>
