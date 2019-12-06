<?php
/**
 * @var Survey $survey
 * @var array $settings
 */
?>
<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3><?php eT("Import responses from a deactivated survey table"); ?></h3>
        <div class="row">
            <div class="col-lg-12 content-right">
                <?php
                  //  echo CHtml::tag('div', array('class' => 'header ui-widget-header'), gT("Import responses from a deactivated survey table"));
                    $this->widget('ext.SettingsWidget.SettingsWidget', array(
                            'settings' => $settings,
                            'method' => 'post',
                            'buttons' => array(
                                gT('Import responses') => array(
                                    'name' => 'ok',
                                    'class' => array('hidden')
                                ),
                                gT('Cancel') => array(
                                    'type' => 'link',
                                    'class' => array('hidden'),
                                    'href' => App()->createUrl('plugins/index')
                                )
                            )
                        ));
                        echo CHtml::openTag('div', array('class' => 'well'));
                        echo CHtml::tag('strong', array('class' => 'text-warning'), gT("Warning"));
                        echo CHtml::tag('p',array(),gT("You can import all old responses that are compatible with your current survey. Compatibility is determined by comparing column types and names, the ID field is always ignored."));
                        echo CHtml::tag('p',array(),gT("Using type coercion may break your data; use with care or not at all if possible."));
                        echo CHtml::tag('p',array(),gT("Currently we detect and handle the following changes:"));
                        $list = array(
                            gT("Question is moved to another group (result is imported correctly)."),
                            gT("Question is removed from target (result is ignored)."),
                            gT("Question is added to target (result is set to database default value).")
                        );
                        CHtml::openTag('ul');
                        foreach ($list as $item) {
                            echo CHtml::tag('li', array(), $item);
                        }
                        CHtml::closeTag('ul');

                    echo CHtml::closeTag('div');
                ?>
        </div>
    </div>
</div>
