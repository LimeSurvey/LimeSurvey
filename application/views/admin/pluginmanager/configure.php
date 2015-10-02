<h3 class="pagetitle"><?php echo sprintf(gT("Settings for plugin: %s"), $plugin['name']); ?></h3>

<?php
    $title = isset($properties['pluginName']) ? sprintf(gT("Settings for plugin: %s"), $properties['pluginName']) : null;
    if (is_null($title)) $title = isset($plugin['name']) ? sprintf(gT("Settings for plugin %s"), $plugin['name']) : null;

    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'settings' => $settings,
        'title' => $title,
        'formHtmlOptions' => array(
            'id' => "pluginsettings-{$plugin['name']}",
        ),
        'method' => 'post',
        'buttons' => array(
            gT('Save plugin settings'),
            gT('Save and return to plugins list')=>array(
                'type'=>'submit',
                'htmlOptions'=>array(
                    'name'=>'redirect',
                    'value'=>App()->createUrl('admin/pluginmanager/sa/index'), // This allow to use App()->request->getPost('redirect')) for forward
                ),
            ),
            gT('Cancel') => array(
                'type' => 'link',
                'href' => App()->createUrl('admin/pluginmanager/sa/index')
            )
        )
    ));
?>
