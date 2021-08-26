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
    <a class="btn btn-danger" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/kill/surveyid/$oSurvey->sid"); ?>" role="button">
        <?php eT("Delete participants table"); ?>
    </a>
<?php endif; ?>

<!-- Download CSV -->
<?php if (!empty($showDownloadButton)) : ?>
    <a class="btn btn-success pull-right" href="#" role="button" id="save-button">
        <span class="fa fa fa-export"></span>
        <?php eT("Download CSV file"); ?>
    </a>
<?php endif; ?>

<!-- Send invitations buttons -->
<?php if (!empty($showSendInvitationButton)) : ?>
    <a class="btn btn-success pull-right" href="#" role="button" id="send-invitation-button">
        <span class="icon-invite"></span>
        <?php eT("Send invitations"); ?>
    </a>
<?php endif; ?>

<!-- Send reminder buttons -->
<?php if (!empty($showSendReminderButton)) : ?>
    <a class="btn btn-success pull-right" href="#" role="button" id="send-reminders-button">
        <span class="icon-invite"></span>
        <?php eT("Send reminders"); ?>
    </a>
<?php endif; ?>

<?php
// Include the default buttons
$this->render('includes/surveyTopbarRight_view', get_defined_vars());
?>
