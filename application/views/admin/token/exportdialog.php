<div class='header ui-widget-header'><?php eT("Token export options"); ?></div>
<?php
$this->widget('ext.SettingsWidget.SettingsWidget', array(
    'settings' => $aSettings,
    'action'=>$sAction,
    'form' => true,
    'title' => gT("Token export options"),
    'buttons' => $aButtons,
));
