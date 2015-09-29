<div class='header ui-widget-header'><?php eT("ls\models\Token export options"); ?></div>
<?php
$this->widget('ext.SettingsWidget.SettingsWidget', array(
    'settings' => $aSettings,
    'action'=>$sAction,
    'form' => true,
    'title' => gT("ls\models\Token export options"),
    'buttons' => $aButtons,
));
