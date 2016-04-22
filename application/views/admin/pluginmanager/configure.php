<h3 class="pagetitle"><?php echo sprintf(gT("Settings for plugin: %s"), $plugin['name']); ?></h3>

<?php
    $title = isset($properties['pluginName']) ? sprintf(gT("Settings for plugin: %s"), $properties['pluginName']) : null;
    if (is_null($title)) $title = isset($plugin['name']) ? sprintf(gT("Settings for plugin %s"), $plugin['name']) : null;

    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'settings' => $settings,
        'formHtmlOptions' => array(
            'id' => "pluginsettings-{$plugin['name']}",
        ),
        'method' => 'post',
        'additionalHtml' => "
            <div class='col-xs-6 col-sm-3'>&nbsp;</div>  <!-- Clear row -->
            <div class='col-sm-5'></div>
            <div class='col-sm-7'>
                 ". (Permission::model()->hasGlobalPermission('settings','update')?"
                <button name='save' class='btn btn-success' type='submit'><span class='glyphicon glyphicon-ok'></span>&nbsp;".gT('Save')."</button>
                <button name='redirect' value='" . App()->createUrl("admin/pluginmanager/sa/index") . "' class='btn btn-default' type='submit'><span class='glyphicon glyphicon-saved'></span>&nbsp;".gT('Save and close')."</button>
                ":'')."
                <a class='btn btn-danger' href='" . App()->createurl('admin/pluginmanager/sa/index') . "'>".gT('Close')."</a>
            </div>"
    ));
?>
