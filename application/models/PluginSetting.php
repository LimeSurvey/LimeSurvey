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
 * This is the model class for table "{{plugin_settings}}".
 */
class PluginSetting extends CActiveRecord {

    /**
     * @param type $className
     * @return PluginSetting
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{plugin_settings}}';
    }
}