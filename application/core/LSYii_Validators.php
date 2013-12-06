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
    /**
    * Filter attribute for isLanguage
    * @var boolean
    */
    public $isLanguage=false;
    /**
    * Filter attribute for isLanguageMulti (multi language string)
    * @var boolean
    */
    public $isLanguageMulti=false;

    public function __construct()
    {
        $this->xssfilter=($this->xssfilter && Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin','read'));
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
            $object->$attribute=html_entity_decode($object->$attribute, ENT_QUOTES, "UTF-8");
        }
        if($this->isLanguage)
        {
            $object->$attribute=$this->languageFilter($object->$attribute);
        }
        if($this->isLanguageMulti)
        {
            $object->$attribute=$this->multiLanguageFilter($object->$attribute);
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
        $filter->options = array(
            'URI.AllowedSchemes'=>array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                )
        );
        return $filter->purify($value);
    }
    /**
    * Defines the customs validation rule for language string
    * 
    * @param mixed $value
    */
    public function languageFilter($value)
    {
        // Maybe use the array of language ?
        return preg_replace('/[^a-z0-9-]/i', '', $value);
    }
    /**
    * Defines the customs validation rule for multi language string
    * 
    * @param mixed $value
    */
    public function multiLanguageFilter($value)
    {
        $aValue=explode(" ",trim($value));
        $aValue=array_map("sanitize_languagecode",$aValue);
        return implode(" ",$aValue);
    }
}
