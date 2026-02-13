<?php
/**
 * This view generate the 'general' tab inside global settings.
 *
 *
 */

?>

<div class="container">
<div class="row">
    <div class="col-6">
        <div class="mb-3">
            <label class=" form-label" for='siteadminemail'><?php eT("Default site admin email:"); ?></label>
            <div class="">
                <input class="form-control" type='email' size='50' id='siteadminemail' name='siteadminemail' value="<?php echo htmlspecialchars((string) getGlobalSetting('siteadminemail')); ?>"/>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='siteadminname'><?php eT("Administrator name:"); ?></label>
            <div class="">
                <input class="form-control" type='text' size='50' id='siteadminname' name='siteadminname' value="<?php echo htmlspecialchars((string) getGlobalSetting('siteadminname')); ?>"/>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='emailmethod'><?php eT("Email method:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'emailmethod',
                    'checkedOption' => Yii::app()->getConfig('emailmethod'),
                    'selectOptions' => [
                        LimeMailer::MethodMail => "PHP",
                        LimeMailer::MethodSmtp => "SMTP",
                        LimeMailer::MethodSendmail => "Sendmail",
                        LimeMailer::MethodQmail => "qmail",
                        LimeMailer::MethodPlugin => "Plugin",
                    ]
                ]); ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for="emailsmtphost"><?php eT("SMTP host:"); ?></label>
            <div class="">
                <input class="form-control" type='text' size='50' aria-describedby="email_lb" id='emailsmtphost' name='emailsmtphost' value="<?php echo htmlspecialchars((string) getGlobalSetting('emailsmtphost')); ?>"/>
                <span  id="email_lb" class="hint"><?php printf(gT("Enter your hostname and port, e.g.: %s"), "smtp.example.org:25"); ?></span>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='emailsmtpuser'><?php eT("SMTP username:"); ?></label>
            <div class="">
                <input class="form-control" type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value="<?php echo htmlspecialchars((string) getGlobalSetting('emailsmtpuser')); ?>"/>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='emailsmtppassword'><?php eT("SMTP password:"); ?></label>
            <div class="">
                <input class="form-control" type='password' autocomplete="off" size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword'/>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='emailsmtpssl'><?php eT("SMTP encryption:"); ?></label>
            <div class="">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget',
                    array(
                        'name' => 'emailsmtpssl',
                        'checkedOption' => getGlobalSetting('emailsmtpssl'),
                        'selectOptions' => array(
                            "" => gT("Off (unsafe)", 'unescaped'),
                            "ssl" => gT("SSL/TLS", 'unescaped'),
                            "tls" => gT("StartTLS", 'unescaped')
                        )
                    )
                ); ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'emailsmtpdebug',
                    'checkedOption' => getGlobalSetting('emailsmtpdebug'),
                    'selectOptions' => [
                        "0" => gT("Off", 'unescaped'),
                        "1" => gT("On errors", 'unescaped'),
                        "2" => gT("Always", 'unescaped'),
                        "3" => gT("Always with connection details", 'unescaped')
                    ]
                ]); ?>
            </div>
        </div>
        <!-- OAuth Plugins -->
        <div class="mb-3">
            <label class="col-12 form-label" for="emailplugin">
                <?php eT("Email plugin:"); ?>
            </label>
            <div class="col-12">
                <select class="form-select" name="emailplugin" id="emailplugin" <?= (Yii::app()->getConfig('emailmethod') == LimeMailer::MethodPlugin) ? '' : 'disabled' ?>>
                    <option value=''><?php eT("None"); ?></option>
                    <?php if (!empty($emailPlugins)): ?>
                        <?php foreach ($emailPlugins as $emailPluginDetails): ?>
                            <option value='<?= $emailPluginDetails->class ?>' <?= ($emailPluginDetails->class == Yii::app()->getConfig('emailplugin')) ? "selected='selected'" : "" ?>>
                                <?= $emailPluginDetails->name ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php
                // TODO: Show message or link to plugin settings if plugin is selected. May need a way to get plugin settings URL from plugin.
            ?>
        </div>
        <div class="mb-3">
            <label class="  form-label" for='maxemails'><?php eT("Email batch size:"); ?></label>
            <div class="">
                <input class="form-control" type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars((string) getGlobalSetting('maxemails')); ?>"/>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="sendingrate"><?php eT("Email sending rate:"); ?></label>
            <div>
                <?php echo CHtml::numberField("sendingrate", App()->getConfig('sendingrate'), array('class' => 'form-control', 'size' => 5, 'min' => 1)); ?>
                <span class="hint"><?php eT("Number of seconds to wait until the next email batch is sent."); ?></span>
            </div>
        </div>
        <!-- Test email -->
        <div class="mb-3">
            <label class="text-start form-label" for='sendTestEmail'>
                <?php eT("Send test email:"); ?>
            </label>
            <div class="">
                <!-- TODO: is this needed? It looks like commented out? -->
                <!--a href="<?php echo \Yii::app()->createUrl('admin/globalsettings', array("sa" => "sendTestEmail")); ?>" class="btn btn-primary btn-large"><?php eT("Send email"); ?></a-->
                <button
                    id="sendtestemailbutton"
                    class='btn btn-large btn-primary'
                    type="button"
                    data-href='<?= \Yii::app()->createUrl('admin/globalsettings', array("sa" => "sendTestEmailConfirmation")) ?>'>
                    <?php eT("Send email"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div id="sendtestemail-confirmation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- the ajax loader -->
        <div class="ajaxloader">
            <p><?php eT('Please wait, loading data...'); ?></p>
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
</div>

<?php if (Yii::app()->getConfig("demoMode") == true): ?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
