<?= TbHtml::formTb(
    null,
    App()->createUrl(
        'plugins/direct',
        ['plugin' => 'TwoFactorAdminLogin', 'function' => 'directCallConfirmKey']
    ),
    'post',
    ["id" => "TFA--modalform"]
) ?>
<div class="modal-header">
    <h5 class="modal-title"><?= gT('Register 2FA Method'); ?></h5>
</div>
<div class="modal-body">
    <div class="row">
        <div class="errorContainer"></div>
    </div>
    <?php echo TbHtml::activeHiddenField($model, 'uid'); ?>
    <div class="mb-3">
        <?php echo TbHtml::activeLabel($model, 'authType', ['class' => 'form-label']); ?>
        <?php echo TbHtml::activeDropDownList($model, 'authType', TFAUserKey::$authTypeOptions, [
            'required' => true,
            'class'    => 'form-select'
        ]); ?>
    </div>
    <div id="totpSection">
        <div class="mb-3">
            <?php echo TbHtml::activeLabel($model, 'secretKey', ['class' => 'form-label']); ?>
            <?php echo TbHtml::activeTextField($model, 'secretKey', ['readonly' => true]); ?>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php eT('QR code'); ?></label>
            <div class="col-md-6 offset-md-3"><?= $sQRCodeContent ?></div>
        </div>
        <div class="mb-3">
            <?php echo TbHtml::label(gT('Confirmation key'), 'confirmationKey', ['class' => 'form-label']); ?>
            <?php echo TbHtml::textField('confirmationKey', null); ?>
        </div>
    </div>
    <div id="yubiSection">
        <div class="mb-3">
            <?php echo TbHtml::label(gT('YubiKey OTP'), 'yubikeyOtp', ['class' => 'form-label']); ?>
            <?php echo TbHtml::textField('yubikeyOtp', null); ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-cancel ls-space margin left-5" id="TFA--cancelform"><?= gT('Cancel') ?></button>
    <button class="btn btn-primary ls-space margin left-5" id="TFA--submitform">
        <?php eT('Confirm & save'); ?>
    </button>
</div>
<?= TbHtml::endForm() ?>
