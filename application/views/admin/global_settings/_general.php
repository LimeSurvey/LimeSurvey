<?php
/**
 * This view generate the 'general' tab inside global settings.
 * 
 */
?>

<ul>
    <li><label for='sitename'><?php eT("Site name:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
        <input type='text' size='50' id='sitename' name='sitename' value="<?php echo htmlspecialchars(getGlobalSetting('sitename')); ?>" /></li>
    <?php

        $thisdefaulttemplate=getGlobalSetting('defaulttemplate');
        $templatenames=array_keys(getTemplateList());

    ?>

    <li><label for="defaulttemplate"><?php eT("Default template:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); 
    
    ?></label>
        <select name="defaulttemplate" id="defaulttemplate">
            <?php
                foreach ($templatenames as $templatename)
                {
                    echo "<option value='$templatename'";
                    if ($thisdefaulttemplate==$templatename) { echo " selected='selected' ";}
                    echo ">$templatename</option>";
                }
            ?>
        </select>
    </li>
    <?php

        $thisadmintheme=getGlobalSetting('admintheme');
        $adminthemes=array_keys(getAdminThemeList());

    ?>
    <li><label for="admintheme"><?php eT("Administration template:"); ?></label>
        <select name="admintheme" id="admintheme">
            <?php
                foreach ($adminthemes as $templatename)
                {
                    echo "<option value='{$templatename}'";
                    if ($thisadmintheme==$templatename) { echo " selected='selected' ";}
                    echo ">{$templatename}</option>";
                }
            ?>
        </select>
    </li>


    <?php $thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode'); ?>
    <li><label for='defaulthtmleditormode'><?php eT("Default HTML editor mode:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
        <select name='defaulthtmleditormode' id='defaulthtmleditormode'>
            <option value='none'
                <?php if ($thisdefaulthtmleditormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("No HTML editor"); ?></option>
            <option value='inline'
                <?php if ($thisdefaulthtmleditormode=='inline') { echo "selected='selected'";} ?>
                ><?php eT("Inline HTML editor (default)"); ?></option>
            <option value='popup'
                <?php if ($thisdefaulthtmleditormode=='popup') { echo "selected='selected'";} ?>
                ><?php eT("Popup HTML editor"); ?></option>
        </select></li>
    <?php $thisdefaultquestionselectormode=getGlobalSetting('defaultquestionselectormode'); ?>
    <li><label for='defaultquestionselectormode'><?php eT("Question type selector:"); echo((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
        <select name='defaultquestionselectormode' id='defaultquestionselectormode'>
            <option value='default'
                <?php if ($thisdefaultquestionselectormode=='default') { echo "selected='selected'";} ?>
                ><?php eT("Full selector (default)"); ?></option>
            <option value='none'
                <?php if ($thisdefaultquestionselectormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("Simple selector"); ?></option>
        </select></li>
    <?php $thisdefaulttemplateeditormode=getGlobalSetting('defaulttemplateeditormode'); ?>
    <li><label for='defaulttemplateeditormode'><?php eT("Template editor:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
        <select name='defaulttemplateeditormode' id='defaulttemplateeditormode'>
            <option value='default'
                <?php if ($thisdefaulttemplateeditormode=='default') { echo "selected='selected'";} ?>
                ><?php eT("Full template editor (default)"); ?></option>
            <option value='none'
                <?php if ($thisdefaulttemplateeditormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("Simple template editor"); ?></option>
        </select></li>
    <?php $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']); ?>
    <li><label for='timeadjust'><?php eT("Time difference (in hours):"); ?></label>
        <span><input type='text' size='10' id='timeadjust' name='timeadjust' value="<?php echo htmlspecialchars(str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60); ?>" />
            <?php echo gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')." - ". gT("Corrected time:").' '.convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'); ?>
        </span></li>

    <li <?php if( ! isset(Yii::app()->session->connectionID)) echo 'style="display: none"';?>><label for='iSessionExpirationTime'><?php eT("Session lifetime for surveys (seconds):"); ?></label>
        <input type='text' size='10' id='iSessionExpirationTime' name='iSessionExpirationTime' value="<?php echo htmlspecialchars(getGlobalSetting('iSessionExpirationTime')); ?>" /></li>
    <li><label for='ipInfoDbAPIKey'><?php eT("IP Info DB API Key:"); ?></label>
        <input type='text' size='35' id='ipInfoDbAPIKey' name='ipInfoDbAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('ipInfoDbAPIKey')); ?>" /></li>
    <li><label for='googleMapsAPIKey'><?php eT("Google Maps API key:"); ?></label>
        <input type='text' size='35' id='googleMapsAPIKey' name='googleMapsAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('googleMapsAPIKey')); ?>" /></li>
    <li><label for='googleanalyticsapikey'><?php eT("Google Analytics API key:"); ?></label>
        <input type='text' size='35' id='googleanalyticsapikey' name='googleanalyticsapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googleanalyticsapikey')); ?>" /></li>
    <li><label for='googletranslateapikey'><?php eT("Google Translate API key:"); ?></label>
        <input type='text' size='35' id='googletranslateapikey' name='googletranslateapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googletranslateapikey')); ?>" /></li>
</ul>

<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
        