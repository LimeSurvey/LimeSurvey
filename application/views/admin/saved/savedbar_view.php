<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <span style='font-weight:bold;'><?php $clang->eT('Saved Responses'); ?></span>
        <?php echo $sSurveyName . ' ' . sprintf($clang->gT('ID: %s'), $iSurveyId); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>

            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/{$iSurveyId}"); ?>" title="<?php $clang->eT('Return to survey administration'); ?>">
                <img name="<?php $clang->eT('Administration'); ?>" src="<?php echo Yii::app()->getConfig('imageurl'); ?>/home.png" alt="<?php echo $clang->eT('Return to survey administration'); ?>" align='left'>
            </a>

        </div>
    </div>
</div>
<div class='header ui-widget-header'>
    <?php $clang->eT('Saved Responses:'); ?> <?php echo getSavedCount($iSurveyId); ?>
</div>