<?php
/**
 * Login Form
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('login');

?>
<noscript>If you see this you have probably JavaScript deactivated. LimeSurvey does not work without Javascript being activated in the browser!</noscript>
<div class="container-fluid welcome">
    <div class="row text-center">
        <div id="login-panel">
            <div class="panel panel-primary login-panel" id="panel-1">

                <!-- Header -->
                <div class="panel-body">
                    <div class="row">
                        <img alt="logo" id="profile-img" class="profile-img-card center-block" src="<?php echo LOGO_URL;?>" />
                        <p><?php eT("Administration");?></p>
                    </div>
                </div>

                <!-- Action Name -->
                <div class="row login-title login-content">
                    <div class="col-lg-12">
                        <h3><?php eT("Set password");?></h3>
                    </div>
                </div>
                <?php if($errorExists){?>
                    <span class="text-warning"><?= $errorMsg?></span><br><br>
                <?php }else{
                ?>
                <!-- Form -->
                <?php echo CHtml::form(array('admin/authentication/sa/newPassword'), 'post', array('id'=>'loginform', 'name'=>'loginform'));?>
                <div class="row login-content login-content-form">
                    <div class="col-lg-12">
                        <?php
                        ?>
                        <div class="row ls-space margin top-5">
                            <label for="password_repeat" class="required" required><?=gT("Password")?> <span class="required">*</span></label>
                            <input name="password" placeholder='********' id="password" class="form-control" type="password">
                        </div>
                        <div class="row ls-space margin top-5">
                            <label for="password_repeat" class="required" required><?=gT("Repeat password")?> <span class="required">*</span></label>
                            <input name="password_repeat" placeholder='********'  id="password_repeat" class="form-control" type="password">
                        </div>
                        <div class="row ls-space margin top-5">
                            <label class="control-label">
                                <?=gT('Random password (suggestion):')?>
                            </label>
                            <input type="text" class="form-control" readonly name="random_example_password" value="<?=htmlspecialchars($randomPassword)?>"/>
                        </div>
                        <input type="hidden" name="validation_key" value="<?= $validationKey?>" >
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row login-submit login-content">
                    <div class="col-lg-12">
                        <p>
                            <button type="submit" class="btn btn-default" name='login_submit' value='login'><?php eT('Save');?></button><br />
                        </p>
                    </div>

                </div>
                <?php echo CHtml::endForm();
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Set focus on user input -->
<script type='text/javascript'>
    $( document ).ready(function() {
        $('#user').focus();
        $("#width").val($(window).width());
    });
    $( window ).resize(function() {
        $("#width").val($(window).width());;
    });
</script>
