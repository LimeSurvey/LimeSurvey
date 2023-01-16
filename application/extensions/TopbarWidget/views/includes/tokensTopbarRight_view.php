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
    <a class="btn btn-danger"
       href="<?php echo Yii::App()->createUrl("admin/tokens/sa/kill/surveyid/$oSurvey->sid"); ?>">
        <?php eT("Delete participants table"); ?>
    </a>
<?php endif; ?>

<!-- Download CSV -->
<?php if (!empty($showDownloadButton)) : ?>
    <a class="btn btn-success float-end" href="#" id="save-button">
        <span class="ri-download-fill"></span>
        <?php eT("Download CSV file"); ?>
    </a>
<?php endif; ?>

<!-- Send invitations buttons -->
<?php if (!empty($showSendInvitationButton)) : ?>
    <a class="btn btn-success float-end" href="#" id="send-invitation-button">
        <span class="ri-mail-send-fill"></span>
        <?php eT("Send invitations"); ?>
    </a>
<?php endif; ?>

<!-- Send reminder buttons -->
<?php if (!empty($showSendReminderButton)) : ?>
    <a class="btn btn-success float-end" href="#" id="send-reminders-button">
        <span class="ri-mail-send-fill"></span>
        <?php eT("Send reminders"); ?>
    </a>
<?php endif; ?>

<?php
// Include the default buttons
$this->render('includes/surveyTopbarRight_view', get_defined_vars());
?>
