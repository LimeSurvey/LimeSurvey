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
            <div class="card card-primary login-panel" id="panel-1">
                <div class="container-fluid">
                <!-- Header -->
                    <div class="card-body">
                        <div class="row">
                            <img alt="logo" id="profile-img" class="profile-img-card img-fluid mx-auto" src="<?php echo LOGO_URL;?>" />
                            <p><?php eT("Administration");?></p>
                        </div>

                        <!-- Action Name -->
                        <div class="row login-title login-content">
                            <div class="col-12">
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
                            <div class="col-12">
                                <?php
                                ?>
                                <span>
                                    <label for="password_repeat" class="required" required><?=gT("Password")?> <span class="required">*</span></label>
                                    <input name="password" placeholder='********' id="password" class="form-control" type="password">
                                </span>
                                <span>
                                    <label for="password_repeat" class="required" required><?=gT("Repeat password")?> <span class="required">*</span></label>
                                    <input name="password_repeat" placeholder='********'  id="password_repeat" class="form-control" type="password">
                                </span>
                                <span>
                                    <label class="form-label">
                                        <?=gT('Random password (suggestion):')?>
                                    </label>
                                    <input type="text" class="form-control" readonly name="random_example_password" value="<?=htmlspecialchars($randomPassword)?>"/>
                                </span>
                                <input type="hidden" name="validation_key" value="<?= $validationKey?>" >
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row login-submit login-content">
                            <div class="col-12">
                                <p>
                                    <button type="submit" class="btn btn-outline-secondary" name='login_submit' value='login'><?php eT('Save');?></button><br />
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
