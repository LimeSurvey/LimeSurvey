<div class="modal-header">
    <h2 class="modal-title h5"><?php eT("Test email settings"); ?></h2>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= gT('Close') ?>"></button>
</div>
<div class="modal-body">
    <p><?php printf(gT("Test email will be sent to: %s"), "<strong>" . $testEmail . "</strong>"); ?></p>
    <div id="settingschangedwarning" class="alert alert-filled-danger" role="alert" style="display: none;">
        <?= gT("There seems to be some changes in the settings which were not saved yet. These changes will be disregarded by the test procedure."); ?>
    </div>
</div>
<div class="modal-footer">
    <a role="button" class="btn btn-primary btn-ok" href="<?php echo \Yii::app()->createUrl('admin/globalsettings', array("sa" => "sendTestEmail")); ?>">
        <span class='ri-check-fill'></span>
        &nbsp;<?php eT("Send email"); ?>
    </a>
    <button role="button" class="btn btn-outline-dark btn-ok"><?php eT("Close"); ?></button>
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
