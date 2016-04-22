<?php
/**
 * This view generate the 'bounce' tab inside global settings.
 *
 */
?>


<div class="form-group">
    <label class="col-sm-4 control-label" for='siteadminbounce'><?php eT("Default site bounce email:"); ?></label>
    <div class="col-sm-6">
        <input class="form-control" type='text' size='50' id='siteadminbounce' name='siteadminbounce' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminbounce')); ?>" />
    </div>
</div>

<div class="form-group">
    <label class="col-sm-4 control-label"  for='bounceaccounttype'><?php eT("Server type:"); ?></label>
    <div class="col-sm-6">
        <select class="form-control" id='bounceaccounttype' name='bounceaccounttype'>
            <option value='off'
                <?php if (getGlobalSetting('bounceaccounttype')=='off') {echo " selected='selected'";}?>
                ><?php eT("Off"); ?></option>
            <option value='IMAP'
                <?php if (getGlobalSetting('bounceaccounttype')=='IMAP') {echo " selected='selected'";}?>
                ><?php eT("IMAP"); ?></option>
            <option value='POP'
                <?php if (getGlobalSetting('bounceaccounttype')=='POP') {echo " selected='selected'";}?>
                ><?php eT("POP"); ?></option>
        </select>
    </div>
</div>


<div class="form-group">
    <label class="col-sm-4 control-label"  for='bounceaccounthost'><?php eT("Server name & port:"); ?></label>
    <div class="col-sm-6">
        <input class="form-control" type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccounthost'))?>" /> <span class='hint'><?php eT("Enter your hostname and port, e.g.: imap.gmail.com:995"); ?></span>
    </div>

</div>

<div class="form-group">
    <label class="col-sm-4 control-label"  for='bounceaccountuser'><?php eT("User name:"); ?></label>
    <div class="col-sm-6">
        <input class="form-control" type='text' size='50' id='bounceaccountuser' name='bounceaccountuser'
            value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccountuser'))?>" />
    </div>
</div>

<div class="form-group">
    <label class="col-sm-4 control-label"  for='bounceaccountpass'><?php eT("Password:"); ?></label>
    <div class="col-sm-6">
        <input class="form-control" type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' />
    </div>
</div>

<div class="form-group">
    <label class="col-sm-4 control-label"  for='bounceencryption'><?php eT("Encryption type:"); ?></label>
    <div class="col-sm-6">
        <select class="form-control" id='bounceencryption' name='bounceencryption'>
            <option value='off'
                <?php if (getGlobalSetting('bounceencryption')=='off') {echo " selected='selected'";}?>
                ><?php eT("Off"); ?></option>
            <option value='SSL'
                <?php if (getGlobalSetting('bounceencryption')=='SSL') {echo " selected='selected'";}?>
                ><?php eT("SSL"); ?></option>
            <option value='TLS'
                <?php if (getGlobalSetting('bounceencryption')=='TLS') {echo " selected='selected'";}?>
                ><?php eT("TLS"); ?></option>
        </select>
    </div>
</div>


<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
