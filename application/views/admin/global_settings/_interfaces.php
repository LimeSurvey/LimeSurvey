<?php
/**
 * This view generate the interface tab inside global settings.
 * 
 */
?>

    <?php $RPCInterface=getGlobalSetting('RPCInterface'); ?>
    <div class="form-group">
            <label class="col-sm-4 control-label"  for='RPCInterface'><?php eT("RPC interface enabled:"); ?></label>
            <div class="col-sm-6">
            <select class="form-control"  id='RPCInterface' name='RPCInterface'>
            <option value='off'
                <?php if ($RPCInterface == 'off') { echo " selected='selected'";}?>
                ><?php eT("Off"); ?></option>
            <option value='json'
                <?php if ($RPCInterface == 'json') { echo " selected='selected'";}?>
                ><?php eT("JSON-RPC"); ?></option>
            <option value='xml'
                <?php if ($RPCInterface == 'xml') { echo " selected='selected'";}?>
                ><?php eT("XML-RPC"); ?></option>
        </select>    
        </div>    
    </div>
            
        <div class="form-group">
            <label class="col-sm-4 control-label" ><?php eT("URL:"); ?></label>
            <div class="col-sm-6">
                    <?php echo $this->createAbsoluteUrl("admin/remotecontrol"); ?>    
        </div>    
    </div>
            
        <?php $rpc_publish_api=getGlobalSetting('rpc_publish_api'); ?>
        <div class="form-group">
            <label class="col-sm-4 control-label"  for='rpc_publish_api'><?php eT("Publish API on /admin/remotecontrol:"); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='rpc_publish_api' name='rpc_publish_api'>
                <option value='1'
                    <?php if ($rpc_publish_api == true) { echo " selected='selected'";}?>
                    ><?php eT("Yes"); ?></option>
                <option value='0'
                    <?php if ($rpc_publish_api == false) { echo " selected='selected'";}?>
                    ><?php eT("No"); ?></option>
            </select>
            
        </div>    
    </div>
            
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>            
