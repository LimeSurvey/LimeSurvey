<?php
/**
* This view generate the interface tab inside global settings.
*
*/
?>

<?php $RPCInterface=getGlobalSetting('RPCInterface'); ?>
<div class="container">
<div class="mb-3">
    <label class=" form-label"  for='RPCInterface'><?php eT("RPC interface enabled:"); ?></label>
    <div>
        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'RPCInterface',
            'checkedOption' => $RPCInterface,
            'selectOptions' => [
                "off"  => gT("Off", 'unescaped'),
                "json" => gT("JSON-RPC", 'unescaped'),
                "xml"  => gT("XML-RPC", 'unescaped')
            ]
        ]); ?>
    </div>
</div>

<div class="mb-3">
    <label class=" form-label" ><?php eT("URL:"); ?></label>
    <div class="">
        <?php echo $this->createAbsoluteUrl("admin/remotecontrol"); ?>
    </div>
</div>

<div class="mb-3">
    <label class=" form-label"  for='rpc_publish_api'><?php eT("Publish API on /admin/remotecontrol:"); ?></label>
    <div>
        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => "rpc_publish_api",
            'checkedOption' => App()->getConfig('rpc_publish_api'),
            'selectOptions' => [
                '1' => gT('On'),
                '0' => gT('Off'),
            ]
        ]); ?>
    </div>
</div>

<div class="mb-3">
    <label class=" form-label"  for='add_access_control_header'><?php eT("Set Access-Control-Allow-Origin header:"); ?></label>
    <div>
        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'add_access_control_header',
            'checkedOption' => App()->getConfig('add_access_control_header'),
            'selectOptions' => [
                '1' => gT('On'),
                '0' => gT('Off'),
            ]
        ]) ?>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php endif; ?>
</div>