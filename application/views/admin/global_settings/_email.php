<?php
/**
 * This view generate the 'general' tab inside global settings.
 *
 *
 */
?>


        <div class="form-group">
            <label class="col-sm-4 control-label"  for='siteadminemail'><?php eT("Default site admin email:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control" type='email' size='50' id='siteadminemail' name='siteadminemail' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminemail')); ?>" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-4  control-label"  for='siteadminname'><?php eT("Administrator name:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='50' id='siteadminname' name='siteadminname' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminname')); ?>" /><br /><br />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='emailmethod'><?php eT("Email method:"); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='emailmethod' name='emailmethod'>
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
        </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for="emailsmtphost"><?php eT("SMTP host:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='50' id='emailsmtphost' name='emailsmtphost' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtphost')); ?>" />&nbsp;<span class='hint'><?php eT("Enter your hostname and port, e.g.: my.smtp.com:25"); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='emailsmtpuser'><?php eT("SMTP username:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtpuser')); ?>" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='emailsmtppassword'><?php eT("SMTP password:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='password' autocomplete="off" size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword' />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='emailsmtpssl'><?php eT("SMTP SSL/TLS:"); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='emailsmtpssl' name='emailsmtpssl'>
            <option value=''
                <?php if (getGlobalSetting('emailsmtpssl')=='') { echo "selected='selected'";} ?>
                ><?php eT("Off"); ?></option>
            <option value='ssl'
                <?php if (getGlobalSetting('emailsmtpssl')=='ssl' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                ><?php eT("SSL"); ?></option>
            <option value='tls'
                <?php if (getGlobalSetting('emailsmtpssl')=='tls') { echo "selected='selected'";} ?>
                ><?php eT("TLS"); ?></option>
        </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='emailsmtpdebug' name='emailsmtpdebug'>
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
        </select><br />&nbsp;
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4  control-label"  for='maxemails'><?php eT("Email batch size:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars(getGlobalSetting('maxemails')); ?>" />
            </div>
        </div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>