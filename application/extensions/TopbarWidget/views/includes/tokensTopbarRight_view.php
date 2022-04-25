<?php
/**
 * @var bool $showDelButton
 * @var bool $hasSurveySettingsUpdatePermission
 * @var bool $hasTokensDeletePermission
 * @var Survey $oSurvey
 */

?>

<!-- Delete tokens table -->
<?php if (!empty($showDelButton) && ($hasSurveySettingsUpdatePermission || $hasTokensDeletePermission)) : ?>
    <button 
        class="btn btn-danger"
        href="<?php echo Yii::App()->createUrl("admin/tokens/sa/kill/surveyid/$oSurvey->sid"); ?>"
        type="button">
        <?php eT("Delete participants table"); ?>
    </button>
<?php endif; ?>

<!-- Download CSV -->
<?php if (!empty($showDownloadButton)) : ?>
    <button class="btn btn-success pull-right" href="#" type="button" id="save-button">
        <span class="fa fa fa-export"></span>
        <?php eT("Download CSV file"); ?>
    </button>
<?php endif; ?>

<!-- Send invitations buttons -->
<?php if (!empty($showSendInvitationButton)) : ?>
    <button class="btn btn-success pull-right" href="#" type="button" id="send-invitation-button">
        <span class="icon-invite"></span>
        <?php eT("Send invitations"); ?>
    </button>
<?php endif; ?>

<!-- Send reminder buttons -->
<?php if (!empty($showSendReminderButton)) : ?>
    <button class="btn btn-success pull-right" href="#" type="button" id="send-reminders-button">
        <span class="icon-invite"></span>
        <?php eT("Send reminders"); ?>
    </button>
<?php endif; ?>

<?php
// Include the default buttons
$this->render('includes/surveyTopbarRight_view', get_defined_vars());
?>
