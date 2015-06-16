<?php

if (!defined('BASEPATH'))
    die('No direct script access allowed');
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

class Template extends LSActiveRecord
{

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{templates}}';
    }

    /**
     * Returns this table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'folder';
    }

    /**
    * Filter the template name : test if template if exist
    *
    * @param string $sTemplateName
    * @return string existing $sTemplateName
    */
    public static function templateNameFilter($sTemplateName)
    {
        $sDefaulttemplate=Yii::app()->getConfig('defaulttemplate','default');
        $sTemplateName=empty($sTemplateName) ? $sDefaulttemplate : $sTemplateName;

        /* Validate it's a real dir included in template allowed dir 
        *  Alternative : use realpath("$dir/$sTemplateName")=="$dir/$sTemplateName" and is_dir
        */
        if(array_key_exists($sTemplateName,self::getTemplateList()))
            return $sTemplateName;

        // If needed recall the function with default template
        if($sTemplateName!=$sDefaulttemplate)
            return self::templateNameFilter($sDefaulttemplate);

        // Last solution is default
        return 'default';
    }

    /**
    * Get the template path for any template : test if template if exist
    *
    * @param string $sTemplateName
    * @return string template path
    */
    public static function getTemplatePath($sTemplateName = "")
    {
        static $aTemplatePath=array();
        if(isset($aTemplatePath[$sTemplateName]))
            return $aTemplatePath[$sTemplateName];

        $sFilteredTemplateName=self::templateNameFilter($sTemplateName);
        if (self::isStandardTemplate($sFilteredTemplateName))
        {
            return $aTemplatePath[$sTemplateName]=Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$sFilteredTemplateName;
        }
        else
        {
            return $aTemplatePath[$sTemplateName]=Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$sFilteredTemplateName;
        }
    }

    /**
    * This function returns the complete URL path to a given template name
    *
    * @param string $sTemplateName
    * @return string template url
    */
    public static function getTemplateURL($sTemplateName="")
    {
        static $aTemplateUrl=array();
        if(isset($aTemplateUrl[$sTemplateName]))
            return $aTemplateUrl[$sTemplateName];

        $sFiteredTemplateName=self::templateNameFilter($sTemplateName);
        if (self::isStandardTemplate($sFiteredTemplateName))
        {
            return $aTemplateUrl[$sTemplateName]=Yii::app()->getConfig("standardtemplaterooturl").'/'.$sFiteredTemplateName;
        }
        else
        {
            return $aTemplateUrl[$sTemplateName]=Yii::app()->getConfig("usertemplaterooturl").'/'.$sFiteredTemplateName;
        }
    }

    public static function getTemplateList()
    {
        $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
        $standardtemplaterootdir=Yii::app()->getConfig("standardtemplaterootdir");

        $aTemplateList=array();

        if ($handle = opendir($standardtemplaterootdir))
        {
            while (false !== ($file = readdir($handle)))
            {
                // Why not return directly standardTemplate list ?
                if (!is_file("$standardtemplaterootdir/$file") && self::isStandardTemplate($file))
                {
                    $aTemplateList[$file] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$file;
                }
            }
            closedir($handle);
        }

        if ($usertemplaterootdir && $handle = opendir($usertemplaterootdir))
        {
            while (false !== ($file = readdir($handle)))
            {
                // Maybe $file[0] != "." to hide Linux hidden directory
                if (!is_file("$usertemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn")
                {
                    $aTemplateList[$file] = $usertemplaterootdir.DIRECTORY_SEPARATOR.$file;
                }
            }
            closedir($handle);
        }
        ksort($aTemplateList);

        return $aTemplateList;
    }

    /**
    * isStandardTemplate returns true if a template is a standard template
    * This function does not check if a template actually exists
    *
    * @param mixed $sTemplateName template name to look for
    * @return bool True if standard template, otherwise false
    */
    public static function isStandardTemplate($sTemplateName)
    {
        return in_array($sTemplateName,
            array(
                'basic',
                'bluengrey',
                'business_grey',
                'citronade',
                'clear_logo',
                'default',
                'eirenicon',
                'limespired',
                'mint_idea',
                'sherpa',
                'vallendar',
            )
        );
    }
}
