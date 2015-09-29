<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <?php use ls\models\SavedControl;

        eT('Saved Responses'); echo ' '. $sSurveyName . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>

            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/{$iSurveyId}"); ?>" title="<?php eT('Return to survey administration'); ?>">
                <img src="<?php echo $sImageURL; ?>/home.png" alt="<?php echo eT('Return to survey administration'); ?>">
            </a>
        </div>
    </div>
</div>
<div class='header ui-widget-header'>
    <?php eT('Saved Responses:'); ?> <?php echo SavedControl::model()->countByAttributes(['sid' => $iSurveyId]); ?>
</div>