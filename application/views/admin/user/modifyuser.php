<?php
/**
 * TODO: unused old user editing page not inside modal, see application/views/userManagement/partial/addedituser.php
 *
 * @var User $oUser
 */
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('modifyUser');
?>

<div class="pagetitle h3">
<?php eT("Editing user");?>
</div>


<div class="container">
    <div class="row" style="margin-bottom: 100px">
      <div class="col-12 content-right">
        <?php $form=$this->beginWidget('TbActiveForm', array(
                'id'    => 'moduserform',
                'action'=> array("admin/user/sa/moduser"),
                'enableAjaxValidation'=>false,
            )); ?>
                <div class="mb-3">
                    <label for="user" class=" form-label">
                        <?php eT("Username");?>
                    </label>
                    <div class="">
                        <?php echo $form->textField($oUser, 'users_name',array('readonly'=>'readonly'));?>
                    </div>
                    <div class="">
                        <span class='text-info'><?php eT("The user name cannot be changed."); ?></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class=" form-label">
                        <?php eT("Email");?>
                    </label>
                    <div class="">
                        <?php echo $form->emailField($oUser,'email');?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="full_name" class=" form-label">
                        <?php eT("Full name");?>
                    </label>
                    <div class="">
                        <?php echo $form->textField($oUser, 'full_name', array('maxlength'=>50));?>
                    </div> 
                </div>

                <?php if( !Permission::model()->hasGlobalPermission('superadmin','read', $oUser->uid) || (Permission::isForcedSuperAdmin(Permission::model()->getUserId())) ): ?>
                <div class="mb-3">
                    <label for="password" class=" form-label">
                        <?php eT("Password");?>
                    </label>
                    <div class="">
                        <?php echo $form->passwordField($oUser, 'password',array('value'=>'', 'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                    </div>
                    <div class="">
                        <span class='text-info'><?php echo $passwordHelpText; ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <p>
                    <input type='submit' class="d-none" value='<?php eT("Save");?>' />
                    <input type='hidden' name='action' value='moduser' />
                    <input type='hidden' name='uid' value="<?php echo $oUser->uid;?>" />
                </p>
            <?php $this->endWidget()?>
        </div>
    </div>
</div>
