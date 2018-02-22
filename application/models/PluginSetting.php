<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Class PluginSetting
 *
 * @property integer $id primary key
 * @property integer $plugin_id see \Plugin
 * @property string $model
 * @property integer $model_id
 * @property string $key
 * @property string $value
 */

class PluginSetting extends CActiveRecord
{

    /**
     * @inheritdoc
     * @return PluginSetting
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{plugin_settings}}';
    }

    /**
     * Returns the validation rules for attributes.
     * @return array[]
     */
    public function rules()
    {
        return array(
            array('plugin_id', 'numerical', 'integerOnly'=>true), // 'allowEmpty'=>false ?
            array('model', 'length', 'max'=>255, 'allowEmpty'=>true),
            array('model_id', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('key', 'length', 'max'=>255),
        );
    }
}
