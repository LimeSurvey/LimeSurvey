<?php
/**
* This view generate the interface tab inside global settings.
*
*/
?>

<?php $RPCInterface=getGlobalSetting('RPCInterface'); ?>
<div class="form-group">
    <label class=" control-label"  for='RPCInterface'><?php eT("RPC interface enabled:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'RPCInterface',
            'value'=> $RPCInterface ,
            'selectOptions'=>array(
                "off"=>gT("Off",'unescaped'),
                "json"=>gT("JSON-RPC",'unescaped'),
                "xml"=>gT("XML-RPC",'unescaped')
            )
        ));?>
    </div>
</div>

<div class="form-group">
    <label class=" control-label" ><?php eT("URL:"); ?></label>
    <div class="">
        <?php echo $this->createAbsoluteUrl("admin/remotecontrol"); ?>
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='rpc_publish_api'><?php eT("Publish API on /admin/remotecontrol:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'name' => 'rpc_publish_api',
            'id'=>'rpc_publish_api',
            'value' => getGlobalSetting('rpc_publish_api'),
            'onLabel'=>gT('On'),
            'offLabel' => gT('Off')));
        ?>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php endif; ?>
