<?php


/**
 * @property string $type
 * @property string $label
 * @property string $help
 * @property string $default
 * @property string $content
 * @property string[] $options
 * Class PluginSetting
 */
class PluginSettingData extends CModel
{

    /**
     * @inheritdoc
     */
    public function attributeNames()
    {
        return array(
            'type'=> gT('Type'),
            'label'=> gT('Label'),
            'help'=> gT('Help'),
            'default'=> gT('Default'),
            'content'=> gT('Content'),
            'options'=> gT('Options'),
        );
    }
}