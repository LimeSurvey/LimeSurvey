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
 * Class Plugin
 *
 * @property integer $id primary key
 * @property string $name
 * @property integer $active
 */
class Plugin extends CActiveRecord {

    /**
     * @param type $className
     * @return Plugin
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
    * Returns the table's name
    *
    * @access public
    * @return string
    */
    public function tableName() {
        return '{{plugins}}';
    }

    /**
    * Returns the validation rules for attributes.
    * @return array[]
    */
    public function rules() {
        return array(
            array('name','length', 'max'=>255),
            array('active','default', 'value'=>0),
            array('active','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        );
    }
}
