<?php
/**
 * Delete Participants Table view
 */
?>
<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <div class="row welcom survey-action">
        <div class="col-lg-12 content-right">
            <div class="jumbotron message-box">
                <h3 class="lead"><?php eT('Delete survey participants table'); ?></h3>
                <p>
            <?php eT('Deleting the participants table will switch the survey back to open-access mode.'); ?>
            <br /> <br />
            <?php eT('Access codes will no longer be required to access this survey.'); ?>
            <br /> <br />
            <?php eT('A backup of this table will be made if you proceed. Your site administrator will be able to access this table.'); ?>
            <br />
            <?php echo '('. $backupTableName .')'; ?>
            <br /> <br />
            <?php eT("You can switch back to closed-access mode at any time. Navigate to Settings --> Survey participants and click on the 'Switch to closed-access mode' button."); ?>
            <br /> <br />
        </p>
        <input
            class="btn btn-default"
            type="submit"
            value="<?php eT('Cancel'); ?>"
            onclick="window.open(
                '<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/{$iSurveyId}"); ?>',
                '_top')" />
        <input 
            class="btn btn-danger"
            type='submit' 
            value="<?php eT('Delete table'); ?>" 
            onclick="window.open(
                '<?php echo $this->createUrl("admin/tokens/sa/kill/surveyid/{$iSurveyId}/ok/Y"); ?>',
                '_top')" />
        
        </div>
</div>