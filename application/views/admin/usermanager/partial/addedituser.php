<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle-addedit"><?=($oUser->isNewRecord ? gT('Add user') : gT('Edit user'))?></h4>
</div>
<div class="modal-body">
    <div class="container-center">
        <?php $form = $this->beginWidget('TbActiveForm', array(
            'id' => 'SMKUserManager--modalform',
            'action' => App()->createUrl('plugins/direct', ['plugin' => 'SMKUserManager', 'function' => 'applyedit']),
            'enableAjaxValidation'=>false,
            'enableClientValidation'=>false,
        ));?>

        <div class="row ls-space margin top-5 bottom-5 hidden" id="SMKUserManager--errors">

        </div>
        <div class="row ls-space margin top-5">
            <?php echo $form->labelEx($oUser,'users_name', ['for' => 'User_Form_users_name']); ?>
            <?php 
                if($oUser->isNewRecord) {
                   echo $form->textField($oUser,'users_name', ['id' => 'User_Form_users_name']);
                } else {
                    echo '<input class="form-control" type="text" name="usernameshim" value="'.$oUser->users_name.'" disabled="true" />';
                }
            ?>

            <?php echo $form->error($oUser,'users_name'); ?>
        </div>
        <div class="row ls-space margin top-5">
            <?php echo $form->labelEx($oUser,'full_name', ['for'=>'User_Form_full_name']); ?>
            <?php echo $form->textField($oUser,'full_name', ['id'=>'User_Form_full_name']); ?>
            <?php echo $form->error($oUser,'full_name'); ?>
        </div>
        <div class="row ls-space margin top-5">
            <?php echo $form->labelEx($oUser,'email', ['for'=>'User_Form_email']); ?>
            <?php echo $form->textField($oUser,'email', ['id'=>'User_Form_email']); ?>
            <?php echo $form->error($oUser,'email'); ?>
        </div>
        <?php if(!$oUser->isNewRecord) { ?> 
        <div class="row ls-space margin top-5">
            <label for="utility_change_password"><?=gT("Change password?")?></label>
            <input type="checkbox" id="utility_change_password">
        </div>
        <?php } ?>
        <div class="row ls-space margin top-5 <?=($oUser->isNewRecord ? '"' : 'hidden" id="utility_change_password_container"')?>>
            <div class="row ls-space margin top-5">
                <?php echo $form->labelEx($oUser,'password', ['for'=>'User_Form_password']); ?>
                <?php echo $form->passwordField(
                    $oUser,
                    'password', 
                    ($oUser->isNewRecord 
                        ? ['id'=>'User_Form_password', 'value' => '', 'placeholder' => '********']
                        : ['id'=>'User_Form_password', 'value' => '', 'placeholder' => '********', "disabled" => "disabled"]
                    )
                ); ?>
                <?php echo $form->error($oUser,'password'); ?>
            </div>
            <div class="row ls-space margin top-5">
                <label for="password_repeat" class="required" required><?=gT("Password safety")?> <span class="required">*</span></label>            
                <input name="password_repeat" placeholder='********' <?=($oUser->isNewRecord ? '' :'disabled="disabled"')?> id="password_repeat" class="form-control" type="password">
            </div>
        </div>
        
        <input type="hidden" value="<?=$oUser->uid?>" name="userid" />
        <div class="row ls-space margin top-5">
            <hr class="ls-space margin top-5 bottom-10"/>
        </div>
        <div class="row ls-space margin top-5">
            <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
            <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>