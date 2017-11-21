<?php
/**
 * This view generate the 'bounce' tab inside global settings.
 *
 */
?>


<div class="form-group">
    <label class=" control-label" for='siteadminbounce'><?php eT("Default site bounce email:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='siteadminbounce' name='siteadminbounce' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminbounce')); ?>" />
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='bounceaccounttype'><?php eT("Server type:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'bounceaccounttype',
                'value'=> getGlobalSetting('bounceaccounttype') ,
                'selectOptions'=>array(
                "off"=>gT("Off",'unescaped'),
                "IMAP"=>gT("IMAP",'unescaped'),
                "POP"=>gT("POP",'unescaped')
                )
                ));?>
    </div>
</div>


<div class="form-group">
    <label class=" control-label"  for='bounceaccounthost'><?php eT("Server name & port:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccounthost'))?>" /> <span class='hint'><?php eT("Enter your hostname and port, e.g.: imap.gmail.com:995"); ?></span>
    </div>

</div>

<div class="form-group">
    <label class=" control-label"  for='bounceaccountuser'><?php eT("User name:"); ?></label>
    <div class="">
        <input class="form-control" type='text' size='50' id='bounceaccountuser' name='bounceaccountuser'
            value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccountuser'))?>" />
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='bounceaccountpass'><?php eT("Password:"); ?></label>
    <div class="">
        <input class="form-control" type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' />
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='bounceencryption'><?php eT("Encryption type:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'bounceencryption',
                'value'=> getGlobalSetting('bounceencryption') ,
                'selectOptions'=>array(
                "off"=>gT("Off",'unescaped'),
                "SSL"=>"SSL",
                "TLS"=>"TLS"
                )
                ));?>
    </div>
</div>


<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
