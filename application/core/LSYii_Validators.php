<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 
class LSYii_Validators extends CValidator {

    /**
    * Filter attribute for XSS
    * @var boolean
    */
    public $xssfilter=true;
    /**
    * Filter attribute for url
    * @var boolean
    */
    public $isUrl=false;

    public function __construct()
    {
        $this->xssfilter=($this->xssfilter && Yii::app()->getConfig('filterxsshtml') && Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1);
    }

    protected function validateAttribute($object,$attribute)
    {
        if($this->xssfilter)
        {
            $object->$attribute=$this->xssFilter($object->$attribute);
        }
        if($this->isUrl)
        {
            if ($object->$attribute== 'http://' || $object->$attribute=='https://') {$object->$attribute="";}
        }
    }
    
    /**
    * Defines the customs validation rule xssfilter
    * 
    * @param mixed $value
    */
    public function xssFilter($value)
    {
        $filter = new CHtmlPurifier();
        $filter->options = array('URI.AllowedSchemes'=>array(
        'http' => true,
        'https' => true,
        ));
        return $filter->purify($value);
    }
}
