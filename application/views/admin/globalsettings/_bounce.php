<?php
/**
 * This view generate the 'bounce' tab inside global settings.
 *
 */
?>

<div class="container">
<div class="row">
<div class="col-6">
<div class="mb-3">
    <label class=" form-label" for='siteadminbounce'><?php eT("Default site bounce email:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='siteadminbounce' name='siteadminbounce' value="<?php echo htmlspecialchars((string) getGlobalSetting('siteadminbounce')); ?>" />
    </div>
</div>

<div class="mb-3">
    <label class=" form-label"  for='bounceaccounttype'><?php eT("Server type:"); ?></label>
    <div>
        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'bounceaccounttype',
            'checkedOption' => getGlobalSetting('bounceaccounttype'),
            'selectOptions' => [
                "off"  => gT("Off", 'unescaped'),
                "IMAP" => gT("IMAP", 'unescaped'),
                "POP"  => gT("POP", 'unescaped')
            ]
        ]); ?>
    </div>
</div>


<div class="mb-3">
    <label class=" form-label"  for='bounceaccounthost'><?php eT("Server name & port:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo htmlspecialchars((string) getGlobalSetting('bounceaccounthost'))?>" />
        <span class='hint'><?php eT("Enter your hostname and port, e.g.: imap.gmail.com:993"); ?></span>
    </div>

</div>

<div class="mb-3">
    <label class=" form-label"  for='bounceaccountuser'><?php eT("User name:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='bounceaccountuser' name='bounceaccountuser'
            value="<?php echo htmlspecialchars((string) getGlobalSetting('bounceaccountuser'))?>" />
    </div>
</div>

<div class="mb-3">
    <label class=" form-label"  for='bounceaccountpass'><?php eT("Password:"); ?></label>
    <div class="">
        <input class="form-control" type='password' size='50' autocomplete="off" id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' />
    </div>
</div>

<div class="mb-3">
    <label class=" form-label"  for='bounceencryption'><?php eT("Encryption type:"); ?></label>
    <div>
        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'bounceencryption',
            'checkedOption' => strtolower((string) getGlobalSetting('bounceencryption')),
            'selectOptions' => [
                "off" => gT("Off (unsafe)", 'unescaped'),
                "ssl" => "SSL/TLS",
                "tls" => "StartTLS"
            ]
        ]); ?>
    </div>
</div>

</div>
</div>
</div>


<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
