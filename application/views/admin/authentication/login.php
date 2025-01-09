<?php

/**
 * Login Form
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('login');

?>
<noscript>If you see this you have probably JavaScript deactivated. LimeSurvey does not work without Javascript being activated in the browser!
</noscript>
<div class="login">
    <div class="row main-body">
        <div class="col-12 col-xl col-right">
            <div class="login-panel">
                <h1><?php eT("Administration"); ?></h1>
                <p><?php eT("Log in"); ?></p>

                <!-- Form -->
                <?php
                echo CHtml::form(['admin/authentication/sa/login'], 'post', ['id' => 'loginform', 'name' => 'loginform']); ?>
                <div class="row login-content login-content-form">
                    <div class="col-12">
                        <?php
                        $pluginNames = array_keys($pluginContent);
                        if (!isset($defaultAuth)) {
                            // Make sure we have a default auth, if not set, use the first one we find
                            $defaultAuth = reset($pluginNames);
                        }

                        if (count($pluginContent) > 1) {
                            $selectedAuth = App()->getRequest()->getParam('authMethod', $defaultAuth);
                            if (!in_array($selectedAuth, $pluginNames)) {
                                $selectedAuth = $defaultAuth;
                            }
                        ?>

                            <label for='authMethod'><?php
                                                    eT("Authentication method"); ?></label>
                        <?php
                            $possibleAuthMethods = [];
                            foreach ($pluginNames as $plugin) {
                                $info = App()->getPluginManager()->getPluginInfo($plugin);
                                $methodName = call_user_func([$info['pluginClass'], 'getAuthMethodName']);
                                $possibleAuthMethods[$plugin] = !empty($methodName) ? $methodName : $info['pluginName'];
                            }
                            //print_r($possibleAuthMethods); die();

                            $this->widget('yiiwheels.widgets.select2.WhSelect2', [
                                'name'          => 'authMethod',
                                'data'          => $possibleAuthMethods,
                                'value'         => $selectedAuth,
                                'pluginOptions' => [
                                    'options' => [
                                        'onChange' => 'this.form.submit();'
                                    ]
                                ]
                            ]);
                        } else {
                            echo CHtml::hiddenField('authMethod', $defaultAuth);
                            $selectedAuth = $defaultAuth;
                        }
                        if (isset($pluginContent[$selectedAuth])) {
                            $blockData = $pluginContent[$selectedAuth];
                            /* @var $blockData PluginEventContent */
                            echo $blockData->getContent();
                        }

                        $aLangList = getLanguageDataRestricted(true);
                        $languageData = [];

                        $reqLang = App()->request->getParam('lang');
                        if ($reqLang === null) {
                            $languageData['default'] = gT('Default');
                        } else {
                            $languageData[$reqLang] = html_entity_decode(
                                (string) $aLangList[$reqLang]['nativedescription'],
                                ENT_NOQUOTES,
                                'UTF-8'
                            ) . " - " . $aLangList[$reqLang]['description'];
                            $languageData['default'] = gT('Default');
                            unset($aLangList[$reqLang]);
                        }

                        foreach ($aLangList as $sLangKey => $aLanguage) {
                            $languageData[$sLangKey] = html_entity_decode(
                                (string) $aLanguage['nativedescription'],
                                ENT_NOQUOTES,
                                'UTF-8'
                            ) . " - " . $aLanguage['description'];
                        }

                        echo '<div id="language-selector">';
                        echo CHtml::label(gT('Language'), 'loginlang');

                        $this->widget('yiiwheels.widgets.select2.WhSelect2', [
                            'name'          => 'loginlang',
                            'data'          => $languageData,
                            'value'         => $language,
                            'pluginOptions' => [
                                'htmlOptions' => [
                                    'id' => 'loginlang'
                                ],
                            ]
                        ]);
                        ?>
                        </div>
                        <?php
                        if (Yii::app()->getConfig("demoMode") === true && Yii::app()->getConfig("demoModePrefill") === true) { ?>
                            <p><?php
                                eT("Demo mode: Login credentials are prefilled - just click the Login button."); ?></p>
                        <?php
                        } ?>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row login-submit login-content">
                    <div class="col-12">
                        <p><input type='hidden' name='action' value='login' />
                            <input type='hidden' id='width' name='width' value='' />
                            <button type="submit" class="btn btn-primary" name='login_submit' value='login'><?php
                                                                                                            eT('Log in'); ?></button>
                        </p>
                        <?php
                        if (Yii::app()->getConfig("display_user_password_in_email") === true) {
                        ?>
                            <a href='<?php
                                        echo $this->createUrl("admin/authentication/sa/forgotpassword"); ?>'><?php
                                                                                                                eT("Forgot your password?"); ?></a><br />
                        <?php
                        }
                        ?>
                    </div>

                </div>
                <?php
                echo CHtml::endForm(); ?>
            </div>
        </div>
        <?php echo Yii::app()->getController()->renderPartial('/admin/authentication/sidebar'); ?>
    </div>
</div>
<!-- Set focus on user input -->
<script type='text/javascript'>
    $(document).ready(function() {
        $('#user').focus();
        $("#width").val($(window).width());
    });
    $(window).resize(function() {
        $("#width").val($(window).width());;
    });
</script>
