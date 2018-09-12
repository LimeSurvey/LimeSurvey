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
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'emailmethod',
            'value'=> getGlobalSetting('emailmethod') ,
            'selectOptions'=>array(
                "mail"=>"PHP",
                "smtp"=>"SMTP",
                "sendmail"=>"Sendmail",
                "qmail"=>"qmail"
            )
        ));?>
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
    <label class="col-sm-4  control-label"  for='emailsmtpssl'><?php eT("SMTP encryption:"); ?></label>
    <div class="col-sm-6">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'emailsmtpssl',
            'value'=> getGlobalSetting('emailsmtpssl') ,
            'selectOptions'=>array(
                ""=>gT("Off",'unescaped'),
                "ssl"=>gT("SSL",'unescaped'),
                "tls"=>gT("TLS",'unescaped')
            )
        ));?>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-4  control-label"  for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
    <div class="col-sm-6">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'emailsmtpdebug',
            'value'=> getGlobalSetting('emailsmtpdebug') ,
            'selectOptions'=>array(
                "0"=>gT("Off",'unescaped'),
                "1"=>gT("On errors",'unescaped'),
                "2"=>gT("Always",'unescaped')
            )
        ));?>

        <br />&nbsp;
    </div>
</div>
<div class="form-group">
    <label class="col-sm-4  control-label"  for='maxemails'><?php eT("Email batch size:"); ?></label>
    <div class="col-sm-2">
        <input class="form-control"  type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars(getGlobalSetting('maxemails')); ?>" />
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php endif; ?>