<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Admin Theme Model
*
*
* @package       LimeSurvey
* @subpackage    Backend
*/
class AdminTheme extends CFormModel
{

    public $name;
    public $path;
    public $config;
    public $sTemplateUrl;

    /** @var Template - The instance of theme object */
    private static $instance;

    /**
     * Get the list of admin theme, as an array containing each configuration object for each template
     * @return array the array of configuration object
     */
    static public function getAdminThemeList()
    {
        $sStandardTemplateRootDir  = Yii::app()->getConfig("styledir");                                       // The directory containing the default admin themes
        $sUserTemplateDir          = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'admintheme';     // The directory containing the user themes

        $aStandardThemeObjects     = self::getThemeList( $sStandardTemplateRootDir );                          // array containing the configuration files of standard admin themes (styles/...)
        $aUserThemeObjects         = self::getThemeList( $sUserTemplateDir  );                                // array containing the configuration files of user admin themes (upload/admintheme/...)
        $aListOfThemeObjects       = array_merge($aStandardThemeObjects, $aUserThemeObjects);

        ksort($aListOfThemeObjects);
        return $aListOfThemeObjects;
    }

    /**
     * Return the necessary datas to load the admin theme
     */
    public function setAdminTheme()
    {
        // We retrieve the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sAdminThemeName           = getGlobalSetting('admintheme');

        $sStandardTemplateRootDir  = Yii::app()->getConfig("styledir");
        $sUserTemplateDir          = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'admintheme';

        // If it's not a standard theme, then it's a user one
        if($this->isStandardAdminTheme($sAdminThemeName))
        {
            $sTemplateDir = $sStandardTemplateRootDir;
            $sTemplateUrl = Yii::app()->getConfig('styleurl').$sAdminThemeName ;
        }
        else
        {
            $sTemplateDir = $sUserTemplateDir;
            $sTemplateUrl = Yii::app()->getConfig('uploadurl').DIRECTORY_SEPARATOR.'admintheme'.DIRECTORY_SEPARATOR.$sAdminThemeName;
        }

        // If the theme directory doesn't exist, it can be that:
        // - user updated from 2.06 and still have old theme configurated in database
        // - user deleted a custom theme
        // In any case, we just set Sea Green as the template to use
        if(!is_dir($sTemplateDir.DIRECTORY_SEPARATOR.$sAdminThemeName))
        {
            $sAdminThemeName   = 'Sea_Green';
            $sTemplateDir      = $sStandardTemplateRootDir;
            $sTemplateUrl      = Yii::app()->getConfig('styleurl').DIRECTORY_SEPARATOR.$sAdminThemeName ;
            setGlobalSetting('admintheme', 'Sea_Green');
        }

        $this->sTemplateUrl = $sTemplateUrl;
        $this->name         = $sAdminThemeName;
        $this->path         = $sTemplateDir . DIRECTORY_SEPARATOR . $this->name;

        // This is necessary because a lot of files still use "adminstyleurl".
        // TODO: replace everywhere the call to Yii::app()->getConfig('adminstyleurl) by $oAdminTheme->sTemplateUrl;
        Yii::app()->setConfig('adminstyleurl', $this->sTemplateUrl );


        // The template configuration.
        $this->config = simplexml_load_file($this->path.'/config.xml');
        $this->defineConstants();
        $this->registerStylesAndScripts();
        return $this;
    }

    /**
     * Register a Css File from the correct directory (publict style, style, upload, etc) using the correct method (with / whithout asset manager)
     *
     * @var string $sPath  'PUBLIC' for /styles-public/, else templates/styles
     * @var string $sFile   the name of the css file
     */
    public function registerCssFile( $sPath='template', $sFile='' )
    {
        if (!YII_DEBUG)
        {
            $path = ($sPath == 'PUBLIC')?dirname(Yii::app()->request->scriptFile).'/styles-public/':$this->path . '/css/';         // We get the wanted constant
            App()->getClientScript()->registerCssFile(  App()->getAssetManager()->publish($path.$sFile) );                         // We publish the asset
        }
        else
        {
            $url = ($sPath == 'PUBLIC')?Yii::app()->getConfig('publicstyleurl'):$this->sTemplateUrl.'/css/';                        // We get the wanted url
            App()->getClientScript()->registerCssFile( $url.$sFile );                                                               // We publish the css file
        }
    }

    /**
     * Register a Css File from the correct directory (publict style, style, upload, etc) using the correct method (with / whithout asset manager)
     *
     * @var string $sPath  'SCRIPT_PATH' for root/scripts/ ; 'ADMIN_SCRIPT_PATH' for root/scripts/admin/; else templates/scripts
     * @var string $sFile   the name of the js file
     */
    public function registerScriptFile( $cPATH, $sFile )
    {
        $bIsTemplatePath = !($cPATH == 'ADMIN_SCRIPT_PATH' || $cPATH == 'SCRIPT_PATH');                                             // we check if the path required is in the template

        if (!$bIsTemplatePath)                                                                                                      // If not, it's or a normal script (like ranking.js) or an admin script
        {
            $sAdminScriptPath = realpath ( Yii::app()->basePath .'/../scripts/admin/') . '/';
            $sScriptPath      =  realpath ( Yii::app()->basePath .'/../scripts/') . '/';
            $path = ($cPATH == 'ADMIN_SCRIPT_PATH')?$sAdminScriptPath:$sScriptPath;                                                 // We get the wanted constant
            $url  = ($cPATH == 'ADMIN_SCRIPT_PATH')?Yii::app()->getConfig('adminscripts'):Yii::app()->getConfig('generalscripts');  // We get the wanted url defined in config
        }
        else
        {
            $path = $this->path.'/scripts/';
            $url  = $this->sTemplateUrl.'/scripts/';
        }

        if (!YII_DEBUG)
        {
            App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( $path . $sFile ));                      // We publish the asset
        }
        else
        {

            App()->getClientScript()->registerScriptFile( $url . $sFile );                                                          // We publish the script
        }
    }

    /**
     * Read an array containing the configuration object of all templates in a given directory
     *
     * @param string $sDir          the directory to scan
     * @return array                the array of object
     */
    static private function getThemeList($sDir)
    {
        $aListOfFiles = array();
        if ($sDir && $pHandle = opendir($sDir))
        {
            while (false !== ($file = readdir($pHandle)))
            {
                if (is_dir($sDir.DIRECTORY_SEPARATOR.$file) && is_file($sDir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'config.xml'))
                {
                    $oTemplateConfig = simplexml_load_file($sDir.DIRECTORY_SEPARATOR.$file.'/config.xml');
                    $aListOfFiles[$file] = $oTemplateConfig;
                }
            }
            closedir($pHandle);
        }
        return $aListOfFiles;
    }

    /**
     * Get instance of theme object.
     * Will instantiate the theme object first time it is called.
     * Please use this instead of global variable.
     * @return AdminTheme
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new self();
            self::$instance->setAdminTheme();
        }
        return self::$instance;
    }

    /**
     * Register all the styles and scripts of the current template
     * Check if RTL is needed
     */
    private function registerStylesAndScripts()
    {
        App()->bootstrap->register();
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('fontawesome');

        // First we get the js files
        foreach($this->config->files->js->filename as $jsfile)
        {
            $this->registerScriptFile( 'template', $jsfile );
        }

        // Then we check if RTL is needed
        if (getLanguageRTL(Yii::app()->language))
        {
            if (!isset($this->config->files->rtl)
                || !isset($this->config->files->rtl->css))
            {
                throw new CException("Invalid template configuration: No CSS files found for right-to-left languages");
            }

            foreach ($this->config->files->rtl->css->filename as $cssfile)
            {
                $this->registerCssFile( 'template', $cssfile );
            }

            // This file is needed for rtl
            $this->registerCssFile( 'template', 'adminstyle-rtl.css' );
        }
        else
        {
            // Non-RTL style
            foreach($this->config->files->css->filename as $cssfile)
            {
                $this->registerCssFile( 'template', $cssfile );
            }
        }
    }

    /**
     * Few constants depending on Template
     */
    private function defineConstants()
    {
        // Define images url
        if(!YII_DEBUG)
        {
            define('LOGO_URL', App()->getAssetManager()->publish( $this->path . '/images/logo.png'));
        }
        else
        {
            define('LOGO_URL', $this->sTemplateUrl.'/images/logo.png');
        }


        // Define presentation text on welcome page
        if($this->config->metadatas->presentation)
        {
            define('PRESENTATION', $this->config->metadatas->presentation);
        }
        else
        {
            define('PRESENTATION', gT('This is the LimeSurvey admin interface. Start to build your survey from here.'));
        }
    }

    private function isStandardAdminTheme($sAdminThemeName)
    {
        return in_array($sAdminThemeName,
            array(
                'Apple_Blossom',
                'Bay_of_Many',
                'Black_Pearl',
                'Dark_Sky',
                'Free_Magenta',
                'Noto_All_Languages',
                'Purple_Tentacle',
                'Sea_Green',
                'Sunset_Orange',
            )
        );
    }
}
