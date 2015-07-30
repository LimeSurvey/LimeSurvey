<?php
/**
 * This view generate the 'general' tab inside global settings.
 * 
 *  
 */
?>

<ul>
    <li><label for='siteadminemail'><?php eT("Default site admin email:"); ?></label>
        <input type='email' size='50' id='siteadminemail' name='siteadminemail' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminemail')); ?>" /></li>

    <li><label for='siteadminname'><?php eT("Administrator name:"); ?></label>
        <input type='text' size='50' id='siteadminname' name='siteadminname' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminname')); ?>" /><br /><br /></li>
    <li><label for='emailmethod'><?php eT("Email method:"); ?></label>
        <select id='emailmethod' name='emailmethod'>
            <option value='mail'
                <?php if (getGlobalSetting('emailmethod')=='mail') { echo "selected='selected'";} ?>
                ><?php eT("PHP (default)"); ?></option>
            <option value='smtp'
                <?php if (getGlobalSetting('emailmethod')=='smtp') { echo "selected='selected'";} ?>
                ><?php eT("SMTP"); ?></option>
            <option value='sendmail'
                <?php if (getGlobalSetting('emailmethod')=='sendmail') { echo "selected='selected'";} ?>
                ><?php eT("Sendmail"); ?></option>
            <option value='qmail'
                <?php if (getGlobalSetting('emailmethod')=='qmail') { echo "selected='selected'";} ?>
                ><?php eT("Qmail"); ?></option>
        </select></li>
    <li><label for="emailsmtphost"><?php eT("SMTP host:"); ?></label>
        <input type='text' size='50' id='emailsmtphost' name='emailsmtphost' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtphost')); ?>" />&nbsp;<span class='hint'><?php eT("Enter your hostname and port, e.g.: my.smtp.com:25"); ?></span></li>
    <li><label for='emailsmtpuser'><?php eT("SMTP username:"); ?></label>
        <input type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtpuser')); ?>" /></li>
    <li><label for='emailsmtppassword'><?php eT("SMTP password:"); ?></label>
        <input type='password' size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword' /></li>
    <li><label for='emailsmtpssl'><?php eT("SMTP SSL/TLS:"); ?></label>
        <select id='emailsmtpssl' name='emailsmtpssl'>
            <option value=''
                <?php if (getGlobalSetting('emailsmtpssl')=='') { echo "selected='selected'";} ?>
                ><?php eT("Off"); ?></option>
            <option value='ssl'
                <?php if (getGlobalSetting('emailsmtpssl')=='ssl' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                ><?php eT("SSL"); ?></option>
            <option value='tls'
                <?php if (getGlobalSetting('emailsmtpssl')=='tls') { echo "selected='selected'";} ?>
                ><?php eT("TLS"); ?></option>
        </select></li>
    <li><label for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
        <select id='emailsmtpdebug' name='emailsmtpdebug'>
            <option value='0'
                <?php
                if (getGlobalSetting('emailsmtpdebug')=='0') { echo "selected='selected'";} ?>
                ><?php eT("Off"); ?></option>
            <option value='1'
                <?php if (getGlobalSetting('emailsmtpdebug')=='1' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                ><?php eT("On errors"); ?></option>
            <option value='2'
                <?php if (getGlobalSetting('emailsmtpdebug')=='2' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                ><?php eT("Always"); ?></option>
        </select><br />&nbsp;</li>
    <li><label for='maxemails'><?php eT("Email batch size:"); ?></label>
        <input type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars(getGlobalSetting('maxemails')); ?>" /></li>
</ul>

<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>