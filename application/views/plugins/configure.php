<?php
    $this->widget('ext.SettingsWidget.SettingsWidget', array(

        'settings' => $settings,
        'title' => isset($plugin['name']) ? sprintf(gT("Settings for plugin %s"), $plugin['name']) : null,
        'formHtmlOptions' => array(
            'id' => "pluginsettings-{$plugin['name']}",
        ),
        'method' => 'post',
        'buttons' => array(
            gT('Save plugin settings'),
            gT('Cancel') => array(
                'type' => 'link',
                'href' => App()->createUrl('plugins/index')
            )
        )
    ));
?>