<?php

/**
 * Second login step: form of the plugin requiring a second authentication factor (e.g. 2FA key)
 *
 * @var PluginEventContent $pluginContent
 * @var string $loginlang
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('secondFactor');

?>
<div class="login">
    <div class="row main-body">
        <div class="col-12 col-xl col-right">
            <div class="login-panel">
                <h1><?php eT("Administration"); ?></h1>
                <p><?php eT("Two-factor authentication"); ?></p>

                <!-- Form -->
                <?php
                echo CHtml::form(['admin/authentication/sa/secondfactor'], 'post', ['id' => 'loginform', 'name' => 'loginform']); ?>
                <div class="row login-content login-content-form">
                    <div class="col-12">
                        <?php echo $pluginContent->getContent(); ?>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row login-submit login-content">
                    <div class="col-12">
                        <p>
                            <?php echo CHtml::hiddenField('loginlang', $loginlang); ?>
                            <input type='hidden' id='width' name='width' value='' />
                            <button type="submit" class="btn btn-primary" name='login_submit' value='login'><?php eT('Log in'); ?></button>
                        </p>
                        <a href='<?php echo $this->createUrl("admin/authentication/sa/login"); ?>'><?php eT("Cancel"); ?></a>
                    </div>
                </div>
                <?php
                echo CHtml::endForm(); ?>
            </div>
        </div>
        <?php echo Yii::app()->getController()->renderPartial('/admin/authentication/sidebar'); ?>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function() {
        $("#width").val($(window).width());
    });
    $(window).resize(function() {
        $("#width").val($(window).width());
    });
</script>
