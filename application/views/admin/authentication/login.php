<?php echo CHtml::form(array('admin/authentication/sa/login'), 'post', array('id'=>'loginform', 'name'=>'loginform'));?>
    <div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php echo $summary; ?></div>
        <br />
        <ul style='width: 500px; margin-left: auto; margin-right: auto'>
            <?php 
            $pluginNames = array_keys($pluginContent);
            if (!isset($defaultAuth)) {
                // Make sure we have a default auth, if not set, use the first one we find
                $defaultAuth = reset($pluginNames);
            }
            if (count($pluginContent)>1) {
                $selectedAuth = App()->getRequest()->getParam('authMethod', $defaultAuth);
                if (!in_array($selectedAuth, $pluginNames)) {
                    $selectedAuth = $defaultAuth;
                }
          ?><li><label for='authMethod'><?php $clang->eT("Authentication method"); ?></label><?php
                $possibleAuthMethods = array();
                foreach($pluginNames as $plugin) {
                    $info = App()->getPluginManager()->getPluginInfo($plugin);
                    $possibleAuthMethods[$plugin] = $info['pluginName'];
                }
                $this->widget('bootstrap.widgets.TbSelect2', array(
                    'name' => 'authMethod',
                    'value' => $selectedAuth,
                    'data' => $possibleAuthMethods,
                    'options' => array(
                        'onChange'=>'this.form.submit();'
                    )
                ));
            } else {
                echo CHtml::hiddenField('authMethod', $defaultAuth);
                $selectedAuth = $defaultAuth;
            }
          ?></li><?php
            if (isset($pluginContent[$selectedAuth])) {
                $blockData = $pluginContent[$selectedAuth];
                /* @var $blockData PluginEventContent */
                echo $blockData->getContent();
            }

            $languageData = array(
                'default' => gT('Default')
            );
            foreach (getLanguageDataRestricted(true) as $sLangKey => $aLanguage)
            {
                $languageData[$sLangKey] =  html_entity_decode($aLanguage['nativedescription'], ENT_NOQUOTES, 'UTF-8') . " - " . $aLanguage['description'];
            }
            echo CHtml::openTag('li');
            echo CHtml::label(gT('Language'), 'loginlang');
            $this->widget('bootstrap.widgets.TbSelect2', array(
                'name' => 'loginlang',
                'data' => $languageData,
                'options' => array(
                    'width' => '230px'
                ),
                'htmlOptions' => array(
                    'id' => 'loginlang'
                ),
                'value' => 'default'
            ));
            echo CHtml::closeTag('li');
            ?>
        </ul>
        <?php   if (Yii::app()->getConfig("demoMode") === true && Yii::app()->getConfig("demoModePrefill") === true)
        { ?>
        <p><?php $clang->eT("Demo mode: Login credentials are prefilled - just click the Login button."); ?></p>
        <?php } ?>

        <p><input type='hidden' name='action' value='login' />
            <input class='action' type='submit' name='login_submit' value='<?php $clang->eT("Login"); ?>' /><br />
            <br/>
            <?php
            if (Yii::app()->getConfig("display_user_password_in_email") === true)
            {
                ?>
                <a href='<?php echo $this->createUrl("admin/authentication/sa/forgotpassword"); ?>'><?php $clang->eT("Forgot your password?"); ?></a><br />
                <?php
            }
            ?>
        </p>
    </div>
<?php echo CHtml::endForm(); ?>
<script type='text/javascript'>
    document.getElementById('user').focus();
</script>
