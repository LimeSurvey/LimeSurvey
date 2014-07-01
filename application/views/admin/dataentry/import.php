<?php
    App()->getClientScript()->registerCssFile(Yii::app()->getBaseUrl() . '/styles/configure.css');
?>

<div class="pluginsettings messagebox">
<?php
    echo CHtml::tag('div', array('class' => 'header ui-widget-header'), gT("Import responses from a deactivated survey table"));
    $this->widget('ext.SettingsWidget.SettingsWidget', array(

            'settings' => $settings,
            'method' => 'post',
            'buttons' => array(
                gT('Import responses') => array(
                    'name' => 'ok'
                ),
                gT('Cancel') => array(
					'type' => 'link',
					'href' => App()->createUrl('plugins/index')
				)
            )
        ));
        echo CHtml::openTag('div', array('class' => 'messagebox ui-corner-all'));
        echo CHtml::tag('div', array('class' => 'warningheader'), gT("Warning"));
        eT("You can import all old responses that are compatible with your current survey. Compatibility is determined by comparing column types and names, the ID field is always ignored.");
		echo '<br/>';
        eT("Using type coercion may break your data; use with care or not at all if possible.");
        echo '<br/>';
		eT("Currently we detect and handle the following changes:");
        
        $list = array(
            
            gT("Question is moved to another group (result is imported correctly)."),
            gT("Question is removed from target (result is ignored)."),
            gT("Question is added to target (result is set to database default value).")
        );
        CHtml::openTag('ul');
        foreach ($list as $item)
        {
            echo CHtml::tag('li', array(), $item);
        }
        CHtml::closeTag('ul');

    echo CHtml::closeTag('div');

?>