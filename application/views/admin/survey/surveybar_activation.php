<?php

/**
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
        <a id='ls-activate-survey' class="btn btn-success" href="<?php echo $this->createUrl("admin/survey/sa/activate/surveyid/$oSurvey->sid"); ?>" role="button">
            <?php eT("Activate this survey"); ?>
        </a>

    <!-- can't activate -->
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveyactivation', 'update')): ?>
        <span class="btntooltip" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button id='ls-activate-survey' type="button" class="btn btn-success btntooltip" disabled="disabled">
                <?php eT("Activate this survey"); ?>
            </button>
        </span>
    <?php endif; ?>
<?php else : ?>

    <!-- activate expired survey -->
    <?php if ($expired) : ?>
        <span class="btntooltip" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" data-html="true" title="<?php eT('This survey is active but expired.'); ?><br><?php eT('Click to adjust.'); ?>">
            <a href='<?php echo $this->createUrl("admin/survey/sa/rendersidemenulink/subaction/publication", ['surveyid' => $oSurvey->sid]); ?>'class="btn btn-success btntooltip" >
                <span class="fa fa-ban">&nbsp;</span>
                <?php eT("Expired"); ?>
            </a>
        </span>
    <?php elseif ($notstarted) : ?>
        <span class="btntooltip" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title='<?php eT("This survey is active but has a start date."); ?>'>
            <button type="button" class="btn btn-success btntooltip" disabled="disabled" >
                <span class="fa fa-clock-o">&nbsp;</span>
                <?php eT("Activate this survey"); ?>
            </button>
        </span>
    <?php endif; ?>

    <!-- Stop survey -->
    <?php if ($canactivate): ?>
        <a class="btn btn-danger btntooltip" href="<?php echo $this->createUrl("admin/survey/sa/deactivate/surveyid/$oSurvey->sid"); ?>" role="button">
            <i class="fa fa-stop-circle" ></i>
            <?php eT("Stop this survey"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>


<!-- Preview/Execute survey -->
<?php if ($oSurvey->isActive || $surveycontent) : ?>

    <!-- Multinlinguage -->
    <?php if (count($oSurvey->allLanguages) > 1): ?>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

            <span class="icon-do" ></span>
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
        <a class="btn btn-default  btntooltip" href="<?php echo $this->createUrl("survey/index", array('sid'=>$oSurvey->sid, 'newtest'=>"Y", 'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank'>
            <span class="icon-do" ></span>
            <?php echo $icontext; ?>
        </a>
    <?php endif; ?>
<?php endif; ?>
