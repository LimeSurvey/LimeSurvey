<?php
/* @var $this AdminController  */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('configurePlugin');
?>
<div class="pagetitle h3"><?php echo sprintf(gT("Settings for plugin: %s"), $plugin['name']); ?></div>

<div class="col-md-10 col-md-offset-1">
    <div class="container-fluid">
<?php
    $title = isset($properties['pluginName']) ? sprintf(gT("Settings for plugin: %s"), $properties['pluginName']) : null;
    if (is_null($title)) $title = isset($plugin['name']) ? sprintf(gT("Settings for plugin %s"), $plugin['name']) : null;

    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'settings' => $settings,
        'formHtmlOptions' => array(
            'id' => "pluginsettings-{$plugin['name']}",
        ),
        'labelWidth'=>4,
        'controlWidth'=>6,
        'method' => 'post',
        'additionalHtml' => "
            <div class='form-group'>
            <div class='col-sm-6 col-md-offset-4'>
                 ". (Permission::model()->hasGlobalPermission('settings','update')?"
                <button name='save' class='btn btn-success' type='submit'><span class='fa fa-floppy-o'></span>&nbsp;".gT('Save')."</button>
                <button name='redirect' value='" . App()->createUrl("admin/pluginmanager/sa/index") . "' class='btn btn-default' type='submit'><span class='fa fa-saved'></span>&nbsp;".gT('Save and close')."</button>
                ":'')."
                <a class='btn btn-danger' href='" . App()->createurl('admin/pluginmanager/sa/index') . "'>".gT('Close')."</a>
            </div>
            </div>"
    ));
?>
    </div>
</div>
