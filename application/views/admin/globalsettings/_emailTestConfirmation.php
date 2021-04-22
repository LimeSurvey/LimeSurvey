<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="h3 modal-title"><?php eT("Test email settings"); ?></div>
</div>
<div class="modal-body">
    <p><?php printf(gT("Test email will be sent to: %s"), "<strong>" . $testEmail . "</strong>"); ?></p>
    <div id="settingschangedwarning" class="jumbotron message-box message-box-error" style="display: none;">
        <p class="text-warning"><?= gT("There seems to be some changes in the settings which were not saved yet. These changes will be disregarded by the test procedure."); ?></p>
    </div>
</div>
<div class="modal-footer">
    <a class="btn btn-primary btn-ok" href="<?php echo \Yii::app()->createUrl('admin/globalsettings', array("sa" => "sendTestEmail"));?>"><span class='fa fa-check'></span>&nbsp;<?php eT("Send email"); ?></a>
    <button type="button" class="btn btn-danger" data-dismiss="modal">
        <?php eT("Close"); ?>
    </button>
</div>

<script>
    var siteadminemail = $('#siteadminemail').val();
    var siteadminname = $('#siteadminname').val();
    var emailmethod = $('#emailmethod input:checked').val();
    var emailsmtphost = $('#emailsmtphost').val();
    var emailsmtpuser = $('#emailsmtpuser').val();
    var emailsmtppassword = $('#emailsmtppassword').val();
    var emailsmtpssl = $('#emailsmtpssl input:checked').val();

    if (siteadminemail != '<?= $siteadminemail ?>' ||
        siteadminname != '<?= $siteadminname ?>' ||
        emailmethod != '<?= $emailmethod ?>' ||
        emailsmtphost != '<?= $emailsmtphost ?>' ||
        emailsmtpuser != '<?= $emailsmtpuser ?>' ||
        emailsmtppassword != '<?= $emailsmtppassword ?>' ||
        emailsmtpssl != '<?= $emailsmtpssl ?>') {
        $('#settingschangedwarning').show();
    }
</script>