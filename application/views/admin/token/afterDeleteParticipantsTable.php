<?php
    /**
     * View which will appear, if the particpants table is deleted.
     */
?>
<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <div class="row welcom survey-action">
        <div class="col-lg-12 content-right">
            <div class="jumbotron message-box">
                <h3 class="lead"><?php eT('Survey participants table deleted'); ?></h3>
                <br /> <br />
                <p>
                    <?php eT('The survey participants table has been deleted and your survey has been switched back to open-access mode. Participants no longer require an access code to access the survey.'); ?>
                    <br /> <br />
                    <?php eT('A backup of this table has been made, which can only be accessed by your site administrator.'); ?>
                    <br />
                    <?php echo '(' . $backupTableName . ')'; ?>
                    <br /> <br />
                    <?php eT("You can switch back to closed-access mode at any time. Navigate to Settings --> Survey participants and click on the 'Switch to closed-access mode' button."); ?>
                    <br /><br />
                    <input
                        class="btn btn-default"
                        type="submit"
                        value="<?php eT('Main Admin Screen'); ?>"
                        onclick="window.open(
                            '<?php echo $this->createUrl("surveyAdministration/view/surveyid/{$iSurveyId}"); ?>',
                            '_top'" />
            </div>
        </div>
    </div>
</div>
