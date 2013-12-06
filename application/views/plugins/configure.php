<?php
    App()->getClientScript()->registerCssFile(Yii::app()->getBaseUrl() . '/styles/configure.css');
?>
<div class="header ui-widget-header"><?php eT('Plugins'); ?></div>
<div id="plugin-<?php echo isset($plugin['name']) ? $plugin['name'] : ''; ?>">
    
    
    <div class="pluginsettings messagebox">
    <?php
        if (isset($plugin['name']))
        {
            echo CHtml::tag('div', array('class'=>'header'), sprintf(gT("Settings for plugin %s"), $plugin['name']));
        }
        $this->widget('ext.SettingsWidget.SettingsWidget', array(

            'settings' => $settings,
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

    </div>
</div>