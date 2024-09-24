<?php

/**
 * Forgot your password
 */

?>
<noscript>If you see this you have probably JavaScript deactivated. LimeSurvey does not work without Javascript being
    activated in the browser!
</noscript>
<div class="login">
    <div class="row main-body">
        <div class="col-12 col-xl col-right">
            <div class="login-panel">
                <h1><?php eT("Administration"); ?></h1>
                <p><?php eT("Recover your password"); ?></p>

                <!-- Form -->
                <?php
                echo CHtml::form(
                    array("admin/authentication/sa/forgotpassword"),
                    'post',
                    array('id' => 'forgotpassword', 'name' => 'forgotpassword')
                ); ?>
                <div class="row login-content login-content-form">
                    <div class="col-12">
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' =>  gT('To receive a new password by email you have to enter your user name and original email address.'),
                            'type' => 'info',
                        ]);
                        ?>
                        <span>
                            <label for="user"><?php
                                                eT('Username'); ?></label>
                            <input name="user" id="user" type="text" size="40" maxlength="64" class="form-control ls-important-field" value="" />
                        </span>
                        <span>
                            <label for="email"><?php
                                                eT('Email address'); ?>

                            </label><input name="email" id="email" type="email" size="40" maxlength="254" class="form-control ls-important-field" value="" />
                        </span>

                    </div>
                </div>

                <!-- Buttons -->
                <div class="row login-submit login-content">
                    <div class="col-12">
                        <input type="hidden" name="action" value="forgotpass" />
                        <input class="action btn btn-primary" type="submit" value="<?php
                                                                                    eT('Check data'); ?>" />
                        <br /><br />
                        <a href="<?php
                                    echo $this->createUrl("/admin"); ?>"><?php
                                                                            eT('Main Admin Screen'); ?></a>
                    </div>

                </div>
                <?php
                echo CHtml::endForm(); ?>

            </div>
        </div>
        <?php echo Yii::app()->getController()->renderPartial('/admin/authentication/sidebar'); ?>
    </div>
</div>
