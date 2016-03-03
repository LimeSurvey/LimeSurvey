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

class TemplateException extends Exception {}

class Template extends LSActiveRecord
{

    /** @var Template - The instance of template object */
    private static $instance;

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
        /* Old template */
        if(is_file(Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$sTemplateName.DIRECTORY_SEPARATOR.'startpage.pstpl'))
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

        return is_dir($sTemplatePath);
    }

    /**
     * Return the necessary datas to load the admin theme
     */
    public static function getAdminTheme()
    {
        // We retrieve the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sAdminThemeName = Yii::app()->getConfig('admintheme');
        $sAdminTemplateRootDir=Yii::app()->getConfig("styledir");
        // If the template doesn't exist, set to Default
        $sAdminThemeName = (self::isStandardTemplate($sAdminThemeName ))?$sAdminThemeName:'default';

        $oAdminTheme = new stdClass();

        // If the required admin theme doesn't exist, Sea_Green will be used
        // TODO : check also for upload directory

        $oAdminTheme->name = (is_dir($sAdminTemplateRootDir.DIRECTORY_SEPARATOR.$sAdminThemeName))?$sAdminThemeName:'Sea_Green';

        // The package name eg: lime-bootstrap-Sea_Green
        $oAdminTheme->packagename = 'lime-bootstrap-'.$oAdminTheme->name;

        // The path of the template files eg : /var/www/limesurvey/styles/Sea_Green
        // TODO : add the upload directory for user template
        $oAdminTheme->path = $sAdminTemplateRootDir.DIRECTORY_SEPARATOR.$oAdminTheme->name;

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

    /**
     * This method construct a template object, having all the needed configuration datas.
     * It checks if the required template is a core one or a user one.
     * If it's a user template, it will check if it's an old 2.0x template to provide default configuration values corresponding to the old template system
     * If it's not an old template, it will check if it has a configuration file to load its datas.
     * If it's not the case (template probably doesn't exist), it will load the default template configuration
     * TODO : more tests should be done, with a call to private function _is_valid_template(), testing not only if it has a config.xml, but also id this file is correct, if it has the needed pstpl files, if the files refered in css exist, etc.
     *
     * @param string $sTemplateName     the name of the template to load. The string come from the template selector in survey settings
     * @param integer $iSurveyId        the id of the survey. If
     * @return StdClass
     */
    public static function getTemplateConfiguration($sTemplateName='', $iSurveyId='')
    {
        if ($sTemplateName == '' && $iSurveyId == '')
        {
            throw new TemplateException(gT("Template needs either template name or survey id"));
        }

        if ($sTemplateName=='')
        {
            $oSurvey = Survey::model()->findByPk($iSurveyId);
            $sTemplateName = $oSurvey->template;
        }

        $oTemplate = new stdClass();
        $oTemplate->isStandard = self::isStandardTemplate($sTemplateName);

        // If the template is standard, its root is based on standardtemplaterootdir
        if($oTemplate->isStandard)
        {
            $oTemplate->name = $sTemplateName;
            $oTemplate->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
        }
        // Else, it's a user template, its root is based on usertemplaterootdir
        else
        {
            $oTemplate->name = $sTemplateName;
            $oTemplate->path = Yii::app()->getConfig("usertemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
        }

        // If the template don't have a config file (maybe it has been deleted, or whatever),
        // then, we load the default template
        if(!self::hasConfigFile($oTemplate->path))
        {
            // If it's an imported template from 2.06, we return default values
            if ( self::isOldTemplate($oTemplate->path) )
            {
                $oTemplate->config = simplexml_load_file(Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.'/minimal-config.xml');
                $oTemplate->config->engine->cssframework = null;
            }
            else
            {
                $oTemplate->name = 'default';
                $oTemplate->path = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.$oTemplate->name;
                $oTemplate->config = simplexml_load_file($oTemplate->path.'/config.xml');
            }
        }
        else
        {
            $oTemplate->config = simplexml_load_file($oTemplate->path.'/config.xml');
        }

        // The template configuration.
        $oTemplate->viewPath = $oTemplate->path.DIRECTORY_SEPARATOR.$oTemplate->config->engine->pstpldirectory.DIRECTORY_SEPARATOR;
        $oTemplate->siteLogo = (isset($oTemplate->config->files->logo))?$oTemplate->config->files->logo->filename:'';

        // condition for user's template prior to 160219
        $oTemplate->filesPath = (isset($oTemplate->config->engine->filesdirectory))? $oTemplate->path.DIRECTORY_SEPARATOR.$oTemplate->config->engine->filesdirectory.DIRECTORY_SEPARATOR : $oTemplate->path . '/files/';
        $oTemplate->cssFramework = $oTemplate->config->engine->cssframework;
        $oTemplate->packages = (array) $oTemplate->config->engine->packages->package;
        $oTemplate->otherFiles = self::getOtherFiles($oTemplate->filesPath);
        return $oTemplate;
    }

    /**
     * Return the list of ALL files present in the file directory
     */
    static public function getOtherFiles($filesdir)
    {
        $otherfiles = array();
        if ( file_exists($filesdir) && $handle = opendir($filesdir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (!is_dir($file))
                {
                    $otherfiles[] = array("name" => $file);
                }
            }
            closedir($handle);
        }
        return $otherfiles;
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
                'news_paper',
                'ubuntu_orange',
            )
        );
    }

    /**
     * Does the given path has a config file ?
     * @param string $sTemplatePath
     * @return bool
     */
    public static function hasConfigFile($sTemplatePath)
    {
        return is_file($sTemplatePath.DIRECTORY_SEPARATOR.'config.xml');
    }

    /**
     * Is the template in that path an old template from 2.0x branch ?
     * @param string $sTemplatePath
     * @return bool
     */
    public static function isOldTemplate($sTemplatePath)
    {
        return (!self::hasConfigFile($sTemplatePath) && is_file($sTemplatePath.DIRECTORY_SEPARATOR.'startpage.pstpl'));
    }

    /**
     * Get instance of template object.
     * Will instantiate the template object first time it is called.
     * Please use this instead of global variable.
     *
     * @param string $sTemplateName
     * @param int $iSurveyId
     * @return Template
     */
    public static function getInstance($sTemplateName='', $iSurveyId='')
    {
        if (empty(self::$instance))
        {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId);
        }
        // This can happen in template editor when we show two templates in one page
        elseif (self::$instance->name !== $sTemplateName)
        {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId);
        }

        return self::$instance;
    }

}
