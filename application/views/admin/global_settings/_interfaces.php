<?php
/**
 * This view generate the interface tab inside global settings.
 * 
 */
?>

<ul>
    <?php $RPCInterface=getGlobalSetting('RPCInterface'); ?>
    <li><label for='RPCInterface'><?php eT("RPC interface enabled:"); ?></label>
        <select id='RPCInterface' name='RPCInterface'>
            <option value='off'
                <?php if ($RPCInterface == 'off') { echo " selected='selected'";}?>
                ><?php eT("Off"); ?></option>
            <option value='json'
                <?php if ($RPCInterface == 'json') { echo " selected='selected'";}?>
                ><?php eT("JSON-RPC"); ?></option>
            <option value='xml'
                <?php if ($RPCInterface == 'xml') { echo " selected='selected'";}?>
                ><?php eT("XML-RPC"); ?></option>
        </select></li>
        <li><label><?php eT("URL:"); ?></label><?php echo $this->createAbsoluteUrl("admin/remotecontrol"); ?></li>
        <?php $rpc_publish_api=getGlobalSetting('rpc_publish_api'); ?>
        <li><label for='rpc_publish_api'><?php eT("Publish API on /admin/remotecontrol:"); ?></label>
            <select id='rpc_publish_api' name='rpc_publish_api'>
                <option value='1'
                    <?php if ($rpc_publish_api == true) { echo " selected='selected'";}?>
                    ><?php eT("Yes"); ?></option>
                <option value='0'
                    <?php if ($rpc_publish_api == false) { echo " selected='selected'";}?>
                    ><?php eT("No"); ?></option>
            </select>
        </li>
</ul>
<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>            
