<div class="modal-header">
    <h2>Register 2FA Method</h2>
</div>
<div class="modal-body">
    <div class="container-center">
        <div class="row">
        <div class="errorContainer"></div>
        </div>
        <?=TbHtml::formTb(null, App()->createUrl('plugins/direct', ['plugin' => 'TwoFactorAdminLogin', 'function' => 'directCallConfirmKey']), 'post', ["id" => "TFA--modalform"])?>
            <?php echo TbHtml::activeHiddenField($model, 'uid'); ?>
            <div class="row ls-space margin bottom-5 top-5">
                <?php echo TbHtml::activeLabel($model, 'authType'); ?>
                <?php echo TbHtml::activeDropDownList($model, 'authType', TFAUserKey::$authTypeOptions, [
                    'required' => true
                ]); ?>
            </div>
            <div class="row ls-space margin bottom-5 top-5">
                <?php echo TbHtml::activeLabel($model, 'secretKey'); ?>
                <?php echo TbHtml::activeTextField($model, 'secretKey', ['readonly' => true]); ?>
            </div>
            <div class="row ls-space margin bottom-5 top-5">
                <label class="col-sm-12 ls-space margin bottom-5"><?php eT('QR code'); ?></label>
                <div class="col-sm-6 col-sm-offset-3"><?=$sQRCodeContent?></div>
            </div>
            <div class="row ls-space margin bottom-5 top-5">
                <?php echo TbHtml::label(gt('Confirmation key'), 'confirmationKey'); ?>
                <?php echo TbHtml::textField('confirmationKey', null, ['required' => true]); ?>
            </div>
            <div class="row ls-space margin bottom-5 top-5">
                <button class="btn btn-success ls-space margin left-5" id="TFA--submitform"><?php eT('Confirm & save'); ?></button>
                <button class="btn btn-error ls-space margin left-5" id="TFA--cancelform"> <?=gT('Cancel')?> </button>
            </div> 
        </form>
    </div>
</div>
