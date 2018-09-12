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
        $aTemplates=self::getTemplateList();
        if (array_key_exists($sTemplateName, $aTemplates))
        {
            return true;
        }
        return false;
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
        $oTemplate = new TemplateConfiguration;
        $oTemplate->setTemplateConfiguration($sTemplateName, $iSurveyId);
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

    /**
    * Returns an array of all available template names - does a basic check if the template might be valid
    *
    */
    public static function getTemplateList()
    {
        $sUserTemplateRootDir=Yii::app()->getConfig("usertemplaterootdir");
        $standardtemplaterootdir=Yii::app()->getConfig("standardtemplaterootdir");

        $aTemplateList=array();

        if ($handle = opendir($standardtemplaterootdir))
        {
            while (false !== ($sFileName = readdir($handle)))
            {
                // Why not return directly standardTemplate list ?
                if (!is_file("$standardtemplaterootdir/$sFileName") && self::isStandardTemplate($sFileName))
                {
                    $aTemplateList[$sFileName] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$sFileName;
                }
            }
            closedir($handle);
        }

        if ($sUserTemplateRootDir && $handle = opendir($sUserTemplateRootDir))
        {
            while (false !== ($sFileName = readdir($handle)))
            {
                // Maybe $file[0] != "." to hide Linux hidden directory
                if (!is_file("$sUserTemplateRootDir/$sFileName") && $sFileName != "." && $sFileName != ".." && $sFileName!=".svn" && (file_exists("{$sUserTemplateRootDir}/{$sFileName}/config.xml") || file_exists("{$sUserTemplateRootDir}/{$sFileName}/startpage.pstpl")))
                {
                    $aTemplateList[$sFileName] = $sUserTemplateRootDir.DIRECTORY_SEPARATOR.$sFileName;
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
        $usertemplaterooturl = Yii::app()->getConfig("usertemplaterooturl");
        $standardtemplaterooturl=Yii::app()->getConfig("standardtemplaterooturl");

        $aTemplateList=array();

        if ($handle = opendir($standardtemplaterootdir))
        {
            while (false !== ($file = readdir($handle)))
            {
                // Why not return directly standardTemplate list ?
                if (!is_file("$standardtemplaterootdir/$file") && self::isStandardTemplate($file))
                {
                    $aTemplateList[$file]['directory'] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$file;
                    $aTemplateList[$file]['preview'] = $standardtemplaterooturl.'/'.$file.'/preview.png';
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
                    $aTemplateList[$file]['preview'] = $usertemplaterooturl.'/'.$file.'/'.'preview.png';
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
     * Get instance of template object.
     * Will instantiate the template object first time it is called.
     * Please use this instead of global variable.
     *
     * @param string $sTemplateName
     * @param int $iSurveyId
     * @return TemplateConfiguration
     */
    public static function getInstance($sTemplateName='', $iSurveyId='')
    {
        if (empty(self::$instance))
        {
            self::$instance = self::getTemplateConfiguration($sTemplateName, $iSurveyId);
        }
        return self::$instance;
    }

    /**
     * Touch each directory in standard template directory to force assset manager to republish them
     */
    public static function forceAssets()
    {
        // Don't touch symlinked assets because it won't work
        if (App()->getAssetManager()->linkAssets) return;
        $standardTemplatesPath = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR;
        $Resource = opendir($standardTemplatesPath);
        while ($Item = readdir($Resource))
        {
            if (is_dir($standardTemplatesPath . $Item) && $Item != "." && $Item != "..")
            {
                touch($standardTemplatesPath . $Item);
            }
        }
    }
}
