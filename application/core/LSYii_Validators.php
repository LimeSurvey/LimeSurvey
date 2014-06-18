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
    * Filter attribute for fixCKeditor
    * @var boolean
    */
    public $fixCKeditor=false;
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
            $object->$attribute=html_entity_decode($object->$attribute, ENT_QUOTES, "UTF-8"); // 140219 : Why not urlencode ?
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
    * Remove some empty characters put by CK editor
    * Did we need to do if user don't use inline HTML editor ?
    * 
    * @param string $value
    */
    public function fixCKeditor($value)
    {
        // Actually don't use it in model : model apply too when import : needed or not ?
        $value = str_replace('<br type="_moz" />','',$value);
        if ($value == "<br />" || $value == " " || $value == "&nbsp;")
        {
            $value = "";
        }
        if (preg_match("/^[\s]+$/",$value))
        {
            $value='';
        }
        if ($value == "\n")
        {
            $value = "";
        }
        if (trim($value) == "&nbsp;" || trim($value)=='')
        { // chrome adds a single &nbsp; element to empty fckeditor fields
            $value = "";
        }
        return $value;
    }
    /**
    * Remove any script or dangerous HTML 
    * 
    * @param string $value
    */
    public function xssFilter($value)
    {
        $filter = new CHtmlPurifier();
        $filter->options = array(
            'AutoFormat.RemoveEmpty'=>false,
            'CSS.AllowTricky'=>true, // Allow display:none; (and other)
            'HTML.SafeObject'=>true, // To allow including youtube
            'Output.FlashCompat'=>true,
            'Attr.EnableID'=>true, // Allow to set id
            'Attr.AllowedFrameTargets'=>array('_blank','_self'),             
            'URI.AllowedSchemes'=>array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                )
        );
        // To allow script BUT purify : HTML.Trusted=true (plugin idea for admin or without XSS filtering ?)
        Yii::import('application.helpers.expressions.em_core_helper');// Already imported in em_manager_helper.php ?
        $oExpressionManager= new ExpressionManager;
        $aValues=$oExpressionManager->asSplitStringOnExpressions($value);// Return array of array : 0=>the string,1=>string length,2=>string type (STRING or EXPRESSION)
        $sNewValue="";
        foreach($aValues as $aValue){
            if($aValue[2]=="STRING")
                $sNewValue.=$filter->purify($aValue[0]);
            else
            {
                $sExpression=trim($aValue[0], '{}');
                $sNewValue.="{";
                $aParsedExpressions=$oExpressionManager->Tokenize($sExpression,true);// Return array of array : 0=>the string,1=>string length,2=>string type 
                foreach($aParsedExpressions as $aParsedExpression)
                {
                    if($aParsedExpression[2]=='DQ_STRING')
                        $sNewValue.="\"".$filter->purify($aParsedExpression[0])."\"";
                    elseif($aParsedExpression[2]=='SQ_STRING')
                        $sNewValue.="'".$filter->purify($aParsedExpression[0])."'";
                    else
                        $sNewValue.=$aParsedExpression[0];
                }
                $sNewValue.="}";
            }
        }
        return $sNewValue;
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
