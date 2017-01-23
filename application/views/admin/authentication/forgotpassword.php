<?php
/**
 * Forgot your password
 */
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
                    </div>
                </div>

                <!-- Action Name -->
                <div class="row login-title login-content">
                      <div class="col-lg-12">
                       <h3><?php eT('Recover your password'); ?></h3>
                    </div>
                </div>

                <!-- Form -->
                <?php echo CHtml::form(array("admin/authentication/sa/forgotpassword"), 'post', array('id'=>'forgotpassword','name'=>'forgotpassword'));?>
                    <div class="row login-content login-content-form">
                        <div class="col-lg-12">
                            <div class="alert alert-info" role="alert">
                                <?php eT('To receive a new password by email you have to enter your user name and original email address.'); ?>
                            </div>
                            <span>
                                <label for="user"><?php eT('User name'); ?></label>
                                <input name="user" id="user" type="text"  size="40" maxlength="64" class="form-control" value="" />
                            </span>
                            <span>
                                <label for="email"><?php eT('Email'); ?>

                                </label><input name="email" id="email" type="email"  size="40" maxlength="254" class="form-control" value="" />
                            </span>

                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row login-submit login-content">
                        <div class="col-lg-12">
                            <input type="hidden" name="action" value="forgotpass" />
                            <input class="action btn btn-default" type="submit" value="<?php eT('Check data'); ?>" />
                            <br/><br/>
                            <a href="<?php echo $this->createUrl("/admin"); ?>"><?php eT('Main Admin Screen'); ?></a>
                        </div>

                    </div>
                <?php echo CHtml::endForm(); ?>
            </div>
        </div>
    </div>
</div>
