<?php
/**
 * Login Form
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('login');

?>
<noscript>If you see this you have probably JavaScript deactivated. LimeSurvey does not work without Javascript being
    activated in the browser!
</noscript>
<div class="login">
    <div class="row main-body">
        <div class="col-12 col-xl col-right">
            <div class="login-panel">
                <h1><?php eT("Administration"); ?></h1>
                <p><?php eT("Set password"); ?></p>
                <?php
                if ($errorExists) { ?>
                    <span class="text-danger"><?= $errorMsg ?></span><br><br>
                    <?php
                } else {
                    ?>
                    <!-- Form -->
                    <?php
                    echo CHtml::form(['admin/authentication/sa/newPassword'],
                        'post',
                        ['id' => 'loginform', 'name' => 'loginform']); ?>
                    <div class="row login-content login-content-form">
                        <div class="col-12">
                            <?php
                            ?>
                            <span>
                                    <label for="password" class="required" required><?= gT("Password") ?> <span
                                                class="required">*</span></label>
                                    <input name="password" placeholder='********' id="password" class="form-control ls-important-field"
                                           type="password">
                                </span>
                            <span>
                                    <label for="password_repeat" class="required" required><?= gT("Repeat password") ?> <span
                                                class="required">*</span></label>
                                    <input name="password_repeat" placeholder='********' id="password_repeat"
                                           class="form-control ls-important-field" type="password">
                                </span>
                            <span>
                                    <label class="form-label">
                                        <?= gT('Random password (suggestion):') ?>
                                    </label>
                                    <input type="text" class="form-control" readonly name="random_example_password"
                                           value="<?= htmlspecialchars((string) $randomPassword) ?>"/>
                                </span>
                            <input type="hidden" name="validation_key" value="<?= CHtml::encode($validationKey) ?>">
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row login-submit login-content">
                        <div class="col-12">
                            <p>
                                <button type="submit" class="btn btn-outline-secondary" name='login_submit'
                                        value='login'><?php
                                    eT('Save'); ?></button>
                                <br/>
                            </p>
                        </div>

                    </div>
                    <?php
                    echo CHtml::endForm();
                }
                ?>
            </div>
        </div>
        <?php echo Yii::app()->getController()->renderPartial('/admin/authentication/sidebar'); ?>
    </div>
</div>

<!-- Set focus on user input -->
<script type='text/javascript'>
    $(document).ready(function () {
        $('#user').focus();
        $("#width").val($(window).width());
    });
    $(window).resize(function () {
        $("#width").val($(window).width());
        ;
    });
</script>
