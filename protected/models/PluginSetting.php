<?php
namespace ls\models;

use CActiveRecord;

/**
 * This is the model class for table "{{plugin_settings}}".
 */
class PluginSetting extends CActiveRecord
{

    public function tableName()
    {
        return '{{plugin_settings}}';
    }
}