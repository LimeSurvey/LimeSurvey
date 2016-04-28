<?php
/**
 * This view generate the 'general' tab inside global settings.
 *
 */
?>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for='sitename'><?php eT("Site name:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='50' id='sitename' name='sitename' value="<?php echo htmlspecialchars(getGlobalSetting('sitename')); ?>" />
        </div>
    </div>

    <?php

        $thisdefaulttemplate=getGlobalSetting('defaulttemplate');
        $templatenames=array_keys(getTemplateList());

    ?>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for="defaulttemplate">
            <?php eT("Default template:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':'');?>
        </label>
        <div class="col-sm-6">
            <select class="form-control"  name="defaulttemplate" id="defaulttemplate">
            <?php
                foreach ($templatenames as $templatename)
                {
                    echo "<option value='$templatename'";
                    if ($thisdefaulttemplate==$templatename) { echo " selected='selected' ";}
                    echo ">$templatename</option>";
                }
            ?>
        </select>

        </div>
    </div>

    <?php
        $thisadmintheme=getGlobalSetting('admintheme');
        $adminthemes=getAdminThemeList();
    ?>
    <div class="form-group">
    <label class="col-sm-4 control-label"  for="admintheme">
        <?php eT("Administration template:"); ?>
    </label>
    <div class="col-sm-6">
        <select class="form-control" name="admintheme" id="admintheme">
            <?php
                foreach ($adminthemes as $templatename => $templateconfig)
                {
                    echo "<option value='{$templatename}'";
                    if ($thisadmintheme==$templatename) { echo " selected='selected' ";}
                    echo ">{$templateconfig->metadatas->name}</option>";
                }
            ?>
        </select>

    </div>
    </div>




    <!-- <input type="hidden" name='admintheme' value='Apple_Blossom' /> -->



    <?php $thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"  for='defaulthtmleditormode'><?php eT("Default HTML editor mode:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  name='defaulthtmleditormode' id='defaulthtmleditormode'>
            <option value='none'
                <?php if ($thisdefaulthtmleditormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("No HTML editor"); ?></option>
            <option value='inline'
                <?php if ($thisdefaulthtmleditormode=='inline') { echo "selected='selected'";} ?>
                ><?php eT("Inline HTML editor (default)"); ?></option>
            <option value='popup'
                <?php if ($thisdefaulthtmleditormode=='popup') { echo "selected='selected'";} ?>
                ><?php eT("Popup HTML editor"); ?></option>
        </select>
        </div>
    </div>

    <?php $thisdefaultquestionselectormode=getGlobalSetting('defaultquestionselectormode'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"  for='defaultquestionselectormode'><?php eT("Question type selector:"); echo((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  name='defaultquestionselectormode' id='defaultquestionselectormode'>
            <option value='default'
                <?php if ($thisdefaultquestionselectormode=='default') { echo "selected='selected'";} ?>
                ><?php eT("Full selector (default)"); ?></option>
            <option value='none'
                <?php if ($thisdefaultquestionselectormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("Simple selector"); ?></option>
        </select>
        </div>
    </div>

    <?php $thisdefaulttemplateeditormode=getGlobalSetting('defaulttemplateeditormode'); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"  for='defaulttemplateeditormode'><?php eT("Template editor:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  name='defaulttemplateeditormode' id='defaulttemplateeditormode'>
            <option value='default'
                <?php if ($thisdefaulttemplateeditormode=='default') { echo "selected='selected'";} ?>
                ><?php eT("Full template editor (default)"); ?></option>
            <option value='none'
                <?php if ($thisdefaulttemplateeditormode=='none') { echo "selected='selected'";} ?>
                ><?php eT("Simple template editor"); ?></option>
        </select>
        </div>
    </div>

    <?php $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']); ?>
    <div class="form-group">
        <label class="col-sm-4 control-label"  for='timeadjust'><?php eT("Time difference (in hours):"); ?></label>
            <div class="col-sm-6">
                        <span><input class="form-control"  type='text' size='10' id='timeadjust' name='timeadjust' value="<?php echo htmlspecialchars(str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60); ?>" />
            <?php echo gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')." - ". gT("Corrected time:").' '.convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'); ?>
        </span>
        </div>
    </div>


    <div class="form-group" <?php if( ! isset(Yii::app()->session->connectionID)) echo 'style="display: none"';?>>
        <label class="col-sm-4 control-label"  for='iSessionExpirationTime'><?php eT("Session lifetime for surveys (seconds):"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='10' id='iSessionExpirationTime' name='iSessionExpirationTime' value="<?php echo htmlspecialchars(getGlobalSetting('iSessionExpirationTime')); ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for='ipInfoDbAPIKey'><?php eT("IP Info DB API Key:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='35' id='ipInfoDbAPIKey' name='ipInfoDbAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('ipInfoDbAPIKey')); ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for='googleMapsAPIKey'><?php eT("Google Maps API key:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='35' id='googleMapsAPIKey' name='googleMapsAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('googleMapsAPIKey')); ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for='googleanalyticsapikey'><?php eT("Google Analytics API key:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='35' id='googleanalyticsapikey' name='googleanalyticsapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googleanalyticsapikey')); ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label"  for='googletranslateapikey'><?php eT("Google Translate API key:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='35' id='googletranslateapikey' name='googletranslateapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googletranslateapikey')); ?>" />
        </div>
    </div>

    <div class='form-group'>
        <label class='col-sm-4 control-label' for='characterset'><?php eT("Character set for file import/export:") ?></label>
        <div class='col-sm-6'>
            <select class='form-control' name='characterset' id='characterset'>
                <?php foreach ($aEncodings as $code => $charset): ?>
                    <option
                        value='<?php echo $code; ?>'
                        <?php if (array_key_exists($thischaracterset, $aEncodings) && $code == $thischaracterset): ?>
                            selected='selected'
                        <?php elseif (!array_key_exists($thischaracterset, $aEncodings) && $code == "auto"): ?>
                            selected='selected'
                        <?php endif; ?>
                    >
                    <?php echo $charset; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class='col-sm-4 control-label' for='sideMenuBehaviour'><?php eT("Side-menu behaviour:"); ?></label>
        <div class='col-sm-6'>
            <select class='form-control' name='sideMenuBehaviour' id='sideMenuBehaviour'>
                <option value='alwaysClosed' 
                    <?php if ($sideMenuBehaviour == 'alwaysClosed'): echo ' selected'; endif;?>
                ><?php eT("Always closed"); ?></option>
                <option value='adaptive'
                    <?php if ($sideMenuBehaviour == 'adaptive' || empty($sideMenuBehaviour)): echo ' selected'; endif;?>
                ><?php eT("Adaptive"); ?> </option>
                <option value='alwaysOpen'
                    <?php if ($sideMenuBehaviour == 'alwaysOpen'): echo ' selected'; endif;?>
                ><?php eT("Always open"); ?></option>
            </select>
        </div>
    </div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
