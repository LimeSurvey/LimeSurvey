<?php
/**
* @var User $oUser
*/
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('modifyUser');
?>

<div class="pagetitle h3">
<?php eT("Editing user");?>
</div>


<div class="container container-center">
    <div class="row" style="margin-bottom: 100px">
      <div class="col-lg-12 content-right">
        <?php $form=$this->beginWidget('TbActiveForm', array(
                'id'    => 'moduserform',
                'action'=> array("admin/user/sa/moduser"),
                'enableAjaxValidation'=>false,
            )); ?>
                <div class="form-group">
                    <label for="user" class=" control-label">
                        <?php eT("Username");?>
                    </label>
                    <div class="">
                        <?php echo $form->textField($oUser, 'users_name',array('readonly'=>'readonly'));?>
                    </div>
                    <div class="">
                        <span class='text-info'><?php eT("The user name cannot be changed."); ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class=" control-label">
                        <?php eT("Email");?>
                    </label>
                    <div class="">
                        <?php echo $form->emailField($oUser,'email');?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="full_name" class=" control-label">
                        <?php eT("Full name");?>
                    </label>
                    <div class="">
                        <?php echo $form->textField($oUser, 'full_name');?>
                    </div>
                </div>

                <?php if( !Permission::model()->hasGlobalPermission('superadmin','read', $oUser->uid) || (Permission::isForcedSuperAdmin(Permission::getUserId())) ): ?>
                <div class="form-group">
                    <label for="password" class=" control-label">
                        <?php eT("Password");?>
                    </label>
                    <div class="">
                        <?php echo $form->passwordField($oUser, 'password',array('value'=>'', 'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <p>
                    <input type='submit' class="hidden" value='<?php eT("Save");?>' />
                    <input type='hidden' name='action' value='moduser' />
                    <input type='hidden' name='uid' value="<?php echo $oUser->uid;?>" />
                </p>
            <?php $this->endWidget()?>
        </div>
    </div>
</div>
