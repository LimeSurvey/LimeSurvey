<?php
    App()->getClientScript()->registerCssFile(Yii::app()->getBaseUrl() . '/styles/configure.css');
?>
<div id="plugin-<?php echo isset($plugin['name']) ? $plugin['name'] : ''; ?>">
    
    
    <div class="pluginsettings">
    <?php
        if (isset($plugin['name']))
        {
            echo CHtml::tag('h1', array(), sprintf(gT("Settings for plugin %s"), $plugin['name']));
        }
        $this->widget('ext.SettingsWidget.SettingsWidget', array(

            'settings' => $settings,
            'formHtmlOptions' => array(
                'id' => "pluginsettings-{$plugin['name']}",
            ),
            'method' => 'post',
            'buttons' => array(
                gT('Save plugin settings'),
                gT('Cancel')
            )
        ));
    ?>

    </div>
</div>