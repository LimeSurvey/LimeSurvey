<div id='pluginsettings'>
<?php
   if (isset($pluginSettings))
   {
       foreach ($pluginSettings as $id => $plugin)
       {
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'settings' => $plugin['settings'],
                'form' => false,
                'title' => sprintf(gT("Settings for plugin %s"), $plugin['name']),
                'prefix' => "plugin[{$plugin['name']}]"

            ));
//                   foreach ($plugin['settings'] as $name => $setting)
//                   {
//                       $name = "plugin[{$plugin['name']}][$name]";
//                       echo CHtml::tag('li', array(), $PluginSettings->renderSetting($name, $setting, null, true));
//                   }
            }


//               Yii::import('application.helpers.PluginSettingsHelper');
//               $PluginSettings = new PluginSettingsHelper();
    }

?>
</div>