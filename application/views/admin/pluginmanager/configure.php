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
        'additionalHtml' => "
            <div class='col-sm-5'></div>
            <div class='col-sm-7'>
                <input class='btn btn-success' name='yt0' value='Save' type='submit'>
                <button name='redirect' value='" . App()->createUrl("admin/pluginmanager/sa/index") . "' class='btn btn-default' type='submit'>Save and close</button>
                <a class='btn btn-danger' href='" . App()->createurl('admin/pluginmanager/sa/index') . "'>Cancel</a>
            </div>"
    ));
?>
