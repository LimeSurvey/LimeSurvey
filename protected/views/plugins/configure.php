<?php
    /* @var $plugin PluginBase */
    $title = sprintf(gT("Settings for plugin: %s"), $plugin->name);
    
    $this->widget('SettingsWidget', array(
        'prefix' => $plugin->id,
        'settings' => $plugin->getPluginSettings(),
        'title' => $title,
        'formHtmlOptions' => array(
            'id' => "pluginsettings-{$plugin->name}",
        ),
        'method' => 'post',
        'buttons' => array(
            gT('Save plugin settings') => [
                'color' => 'primary',
            ],
            gT('Save and return to plugins list')=>array(
                'type'=>'submit',
                'name'=>'redirect'
            ),
            gT('Cancel') => array(
                'type' => 'link',
                'href' => ['plugins/index']
            )
        )
    ));
?>
