<?php
/**
* @var User $oUser
*/
?>

<div class="pagetitle h3">
    <?php eT("Editing user");?>
</div>

  <div class="container container-center">
    <div class="row" style="margin-bottom: 100px">
      <div class="col-lg-12 content-right">
        <?php echo CHtml::form(array("admin/user/sa/moduser"), 'post', array('name'=>'moduserform', 'id'=>'moduserform','class'=>'')); ?>
          <div class="form-group">
            <label for="user" class=" control-label">
              <?php eT("Username");?>
            </label>
            <div class="">
              <?php echo CHtml::textField('user',$aUserData['users_name'],array('class'=>"form-control",'readonly'=>'readonly'));?>
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
              <?php echo CHtml::emailField('email',$aUserData['email'],array('class'=>"form-control"));?>
            </div>
          </div>
          <div class="form-group">
            <label for="full_name" class=" control-label">
              <?php eT("Full name");?>
            </label>
            <div class="">
              <?php echo CHtml::textField('full_name',$aUserData['full_name'],array('class'=>"form-control"));?>
            </div>
          </div>
          <div class="form-group">
            <label for="password" class=" control-label">
              <?php eT("Password");?>
            </label>
            <div class="">
              <?php echo CHtml::passwordField('password','',array('class'=>"form-control",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                <input type='hidden' name='uid' value="<?php echo $aUserData['uid'];?>" />
            </div>
          </div>

          <p>
            <input type='submit' class="hidden" value='<?php eT("Save");?>' />
            <input type='hidden' name='action' value='moduser' />
          </p>

        <?php echo CHtml::endForm()?>
    </div>
</div>
