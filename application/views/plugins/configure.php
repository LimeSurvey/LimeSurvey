<?php
    App()->getClientScript()->registerCssFile('/styles/configure.css');

?>
<div id="plugin-<?php echo isset($plugin['name']) ? $plugin['name'] : ''; ?>">
    
    
    <div class="pluginsettings">
    <?php
        if (isset($plugin['name']))
        {
            echo CHtml::tag('h1', array(), "Settings for plugin {$plugin['name']}");
        }

        Yii::import("application.helpers.PluginSettingsHelper");
        $PluginSettings = new PluginSettingsHelper();
        
        echo CHtml::beginForm('', 'post', array('id' => "pluginsettings-{$plugin['name']}"));
        echo CHtml::openTag('ol');
        foreach ($settings as $name => $setting)
        {
            echo CHtml::tag('li', array(), $PluginSettings->renderSetting($name, $setting, "pluginsettings-{$plugin['name']}", true));
            
        }
        echo CHtml::closeTag('ol');
        echo CHtml::submitButton('Save plugin settings', array('name'=>'ok'));
        echo CHtml::submitButton('Cancel', array('name'=>'cancel'));
        echo CHtml::endForm();

    ?>

    </div>
</div>