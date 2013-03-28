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
                $selectedAuth = App()->getRequest()->getPost('authMethod', $defaultAuth);
          ?><li><label for='authMethod'><?php $clang->eT("Authentication method"); ?></label><?php
                echo CHtml::dropDownList('authMethod', $selectedAuth, $methods);
            } else {
                echo CHtml::hiddenField('authMethod', $defaultAuth);
                $selectedAuth = $defaultAuth;
            }
            if (isset($pluginContent[$selectedAuth])) {
                $blockData = $pluginContent[$selectedAuth];
                /* @var $blockData PluginEventContent */
                echo CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()), $blockData->getContent());
            }
            ?>
            <li><label for='loginlang'><?php $clang->eT("Language"); ?></label>
                <select id='loginlang' name='loginlang'>
                    <option value="default" selected="selected"><?php $clang->eT('Default'); ?></option>
                    <?php
                    $x = 0;
                    foreach (getLanguageDataRestricted(true) as $sLangKey => $aLanguage)
                    {
                        //The following conditional statements select the browser language in the language drop down box and echoes the other options.
                        ?>
                        <option value='<?php echo $sLangKey; ?>'><?php echo $aLanguage['nativedescription'] . " - " . $aLanguage['description']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </li>
        </ul>
    <p><input type='hidden' name='action' value='login' />
        <input class='action' type='submit' name='login_submit' value='<?php $clang->eT("Login"); ?>' /><br />&nbsp;
        <br/>
        <?php
        if (Yii::app()->getConfig("display_user_password_in_email") === true)
        {
            ?>
            <a href='<?php echo $this->createUrl("admin/authentication/sa/forgotpassword"); ?>'><?php $clang->eT("Forgot your password?"); ?></a><br />&nbsp;
            <?php
        }
        ?>
    </p><br />
    </div></form>
<script type='text/javascript'>
    document.getElementById('user').focus();
</script>
