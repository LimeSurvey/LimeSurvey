<?php
/**
 * This view generate the 'bounce' tab inside global settings.
 * 
 */
?>

<ul>
<li><label for='siteadminbounce'><?php eT("Default site bounce email:"); ?></label>
    <input type='text' size='50' id='siteadminbounce' name='siteadminbounce' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminbounce')); ?>" /></li>
<li><label for='bounceaccounttype'><?php eT("Server type:"); ?></label>
    <select id='bounceaccounttype' name='bounceaccounttype'>
        <option value='off'
            <?php if (getGlobalSetting('bounceaccounttype')=='off') {echo " selected='selected'";}?>
            ><?php eT("Off"); ?></option>
        <option value='IMAP'
            <?php if (getGlobalSetting('bounceaccounttype')=='IMAP') {echo " selected='selected'";}?>
            ><?php eT("IMAP"); ?></option>
        <option value='POP'
            <?php if (getGlobalSetting('bounceaccounttype')=='POP') {echo " selected='selected'";}?>
            ><?php eT("POP"); ?></option>
    </select></li>

<li><label for='bounceaccounthost'><?php eT("Server name & port:"); ?></label>
    <input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccounthost'))?>" /> <span class='hint'><?php eT("Enter your hostname and port, e.g.: imap.gmail.com:995"); ?></span>
</li>
<li><label for='bounceaccountuser'><?php eT("User name:"); ?></label>
    <input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser'
        value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccountuser'))?>" /></li>
<li><label for='bounceaccountpass'><?php eT("Password:"); ?></label>
    <input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' /></li>
<li><label for='bounceencryption'><?php eT("Encryption type:"); ?></label>
    <select id='bounceencryption' name='bounceencryption'>
        <option value='off'
            <?php if (getGlobalSetting('bounceencryption')=='off') {echo " selected='selected'";}?>
            ><?php eT("Off"); ?></option>
        <option value='SSL'
            <?php if (getGlobalSetting('bounceencryption')=='SSL') {echo " selected='selected'";}?>
            ><?php eT("SSL"); ?></option>
        <option value='TLS'
            <?php if (getGlobalSetting('bounceencryption')=='TLS') {echo " selected='selected'";}?>
            ><?php eT("TLS"); ?></option>
    </select></li></ul>

<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
                    