<?=TbHtml::formTb(null, App()->createUrl('plugins/direct', ['plugin' => 'TwoFactorAdminLogin', 'function' => 'directCallConfirmKey']), 'post', ["id" => "TFA--modalform"])?>
    <div class="modal-header">
        <h2><?= gT("Register 2FA Method"); ?></h2>
    </div>
    <div class="modal-body">
        <div class="container-center">
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
                <div class="mb-3">
                    <?php echo TbHtml::activeLabel($model, 'secretKey', ['class' => 'form-label']); ?>
                    <?php echo TbHtml::activeTextField($model, 'secretKey', ['readonly' => true, 'class' => 'form-control']); ?>
                </div>
                <div class="mb-3">
                    <label class="col-12 ls-space margin bottom-5"><?php eT('QR code'); ?></label>
                    <div class="col-md-6 offset-md-3"><?=$sQRCodeContent?></div>
                </div>
                <div class="mb-3">
                    <?php echo TbHtml::label(gT('Confirmation key'), 'confirmationKey', ['class' => 'form-label']); ?>
                    <?php echo TbHtml::textField('confirmationKey', null, ['required' => true, 'class' => 'form-control']); ?>
                </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="mb-3 text-end">
            <button class="btn btn-cancel" id="TFA--cancelform"> <?=gT('Cancel')?> </button>
            <button class="btn btn-success" id="TFA--submitform"><?php eT('Confirm & save'); ?></button>
        </div>
    </div>
</form>
