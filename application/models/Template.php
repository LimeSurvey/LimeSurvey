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

        /* Standard Template return it without testing */
        if(self::isStandardTemplate($sTemplateName))
        {
            return $sTemplateName;
        }
        /* Validate if template is OK in user dir, DIRECTORY_SEPARATOR not needed "/" is OK */
        if(is_file(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$sTemplateName.DIRECTORY_SEPARATOR.'config.xml'))
        {
            return $sTemplateName;
        }
        /* Then try with the global default template */
        if($sTemplateName!=$sDefaulttemplate)
            return self::templateNameFilter($sDefaulttemplate);
        /* Last solution : default */
        return 'default';
    }


    public static function checkIfTemplateExists($sTemplateName)
    {
        $sTemplatePath = self::getTemplatePath($sTemplateName);
        return is_dir($sTemplatePath.'/'.$sTemplateName);
    }

    /**
     * Return the necessary datas to load the admin theme
     */
    public static function getAdminTheme()
    {
        // We retrieve the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sAdminThemeName = Yii::app()->getConfig('admintheme');
        $oAdminTheme = new stdClass();

        // If the required admin theme doesn't exist, Sea_Green will be used
        // TODO : check also for upload directory
        $oAdminTheme->name = (is_dir(Yii::app()->basePath.'/../styles/'.$sAdminThemeName))?$sAdminThemeName:'Sea_Green';

        // The package name eg: lime-bootstrap-Sea_Green
        $oAdminTheme->packagename = 'lime-bootstrap-'.$oAdminTheme->name;

        // The path of the template files eg : /var/www/limesurvey/styles/Sea_Green
        // TODO : add the upload directory for user template
        $oAdminTheme->path = realpath(Yii::app()->basePath.'/../styles/'.$oAdminTheme->name);

        // The template configuration.
        $oAdminTheme->config = simplexml_load_file($oAdminTheme->path.'/config.xml');

        return $oAdminTheme;
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

    public static function getTemplateConfiguration($sTemplateName='', $iSurveyId='')
    {
        if($sTemplateName=='')
        {
            $oSurvey = Survey::model()->findByPk($iSurveyId);
            $sTemplateName = $oSurvey->template;
        }

        $oTemplate = new stdClass();
        $oTemplate->isStandard = self::isStandardTemplate($sTemplateName);
        // If the template doesn't exist, set to Default
        if($oTemplate->isStandard)
        {
            $oTemplate->name = $sTemplateName;
            $oTemplate->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
        }
        else
        {
            if(is_file(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$sTemplateName.DIRECTORY_SEPARATOR.'config.xml'))
            {
                $oTemplate->name = $sTemplateName;
                $oTemplate->path = Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
            }
            else
            {
                $oTemplate->name = 'default';
                $oTemplate->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
            }
        }

        // The template configuration.
        $oTemplate->config = simplexml_load_file($oTemplate->path.'/config.xml');
        $oTemplate->viewPath = $oTemplate->path.DIRECTORY_SEPARATOR.$oTemplate->config->engine->pstpldirectory.DIRECTORY_SEPARATOR;

        $oTemplate->cssFramework = $oTemplate->config->engine->cssframework;
        $oTemplate->packages = (array) $oTemplate->config->engine->packages->package;

        return $oTemplate;
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

    public static function getTemplateListWithPreviews()
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
                    $aTemplateList[$file]['directory'] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$file;
                    $aTemplateList[$file]['preview'] = Yii::app()->request->baseUrl.'/templates/'.$file.'/preview.png';
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
                    $aTemplateList[$file]['directory']  = $usertemplaterootdir.DIRECTORY_SEPARATOR.$file;
                    $aTemplateList[$file]['preview'] = Yii::app()->request->baseUrl.'/upload/templates/'.$file.'/preview.png';
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
                'default',
                'material_design',
                'metro_ode',
                'news_paper',
                'night_mode',
                'ubuntu_orange',
            )
        );
    }
}
