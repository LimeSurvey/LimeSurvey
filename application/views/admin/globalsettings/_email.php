<?php
/**
* This view generate the 'general' tab inside global settings.
*
*
*/
?>

<div class="container-fluid">
    <div class="row">
        <div class="form-group col-sm-12">
            <label class=" control-label"  for='siteadminemail'><?php eT("Default site admin email:"); ?></label>
            <div class="">
                <input class="form-control" type='email' size='50' id='siteadminemail' name='siteadminemail' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminemail')); ?>" />
            </div>
        </div>

        <div class="form-group col-sm-12">
            <label class="  control-label"  for='siteadminname'><?php eT("Administrator name:"); ?></label>
            <div class="">
                <input class="form-control"  type='text' size='50' id='siteadminname' name='siteadminname' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminname')); ?>" /><br /><br />
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <label class="  control-label"  for='emailmethod'><?php eT("Email method:"); ?></label>
            <div class="">
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
            <label class="  control-label"  for="emailsmtphost"><?php eT("SMTP host:"); ?></label>
            <div class="">
                <input class="form-control"  type='text' size='50' id='emailsmtphost' name='emailsmtphost' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtphost')); ?>" />
                <span class="hint"><?php printf(gT("Enter your hostname and port, e.g.: %s"),"smtp.example.org:25"); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="  control-label"  for='emailsmtpuser'><?php eT("SMTP username:"); ?></label>
            <div class="">
                <input class="form-control"  type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtpuser')); ?>" />
            </div>
        </div>
        <div class="form-group">
            <label class="  control-label"  for='emailsmtppassword'><?php eT("SMTP password:"); ?></label>
            <div class="">
                <input class="form-control"  type='password' autocomplete="off" size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword' />
            </div>
        </div>
        <div class="form-group">
            <label class="  control-label"  for='emailsmtpssl'><?php eT("SMTP encryption:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'emailsmtpssl',
                    'value'=> getGlobalSetting('emailsmtpssl') ,
                    'selectOptions'=>array(
                        ""=>gT("Off (unsafe)",'unescaped'),
                        "ssl"=>gT("SSL/TLS",'unescaped'),
                        "tls"=>gT("StartTLS",'unescaped')
                    )
                ));?>
            </div>
        </div>
        <div class="form-group">
            <label class="  control-label"  for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
            <div class="">
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
            <label class="  control-label"  for='maxemails'><?php eT("Email batch size:"); ?></label>
            <div class="">
                <input class="form-control"  type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars(getGlobalSetting('maxemails')); ?>" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="sendingrate"><?php eT("Email sending rate:"); ?></label>
            <div>
                <?php echo CHtml::numberField("sendingrate", App()->getConfig('sendingrate'), array('class' => 'form-control', 'size' => 5, 'min' => 1)); ?>
                <span class="hint"><?php eT("Number of seconds to wait until the next email batch is sent."); ?></span>
            </div>
        </div>
        <!-- Test email -->
        <div class="form-group">
            <label class="text-left control-label" for='sendTestEmail'>
            <?php eT("Send test email:"); ?>
            </label>
            <div class="">
                <!--a href="<?php echo \Yii::app()->createUrl('admin/globalsettings', array("sa"=>"sendTestEmail")); ?>" class="btn btn-success btn-large"><?php eT("Send email");?></a-->
                <button 
                    id="sendtestemailbutton"
                    class='btn btn-large btn-primary' 
                    type="button"
                    data-href='<?= \Yii::app()->createUrl('admin/globalsettings', array("sa"=>"sendTestEmailConfirmation")) ?>'>
                    <?php eT("Send email");?>
                </button>
            </div>
        </div>
    </div>
</div>
<div id="sendtestemail-confirmation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- the ajax loader -->
        <div class="ajaxloader">
            <p><?php eT('Please wait, loading data...');?></p>
            <div class="preloader loading">
                <span class="slice"></span>
                <span class="slice"></span>
                <span class="slice"></span>
                <span class="slice"></span>
                <span class="slice"></span>
                <span class="slice"></span>
            </div>
        </div>
        <!-- Modal content-->
        <div class="modal-content">
            
        </div>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php endif; ?>
