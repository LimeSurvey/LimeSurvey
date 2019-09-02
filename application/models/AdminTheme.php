<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
    /** @var string $name Admin Theme's name */
    public $name;
    /** @var string $path Admin Theme's path */
    public $path;
    /** @var string $sTemplateUrl URL to reach Admin Theme (used to get CSS/JS/Files when asset manager is off) */
    public $sTemplateUrl;
    /** @var mixed $config Contains the Admin Theme's configuration file */
    public $config;
    /** @var boolean $use_asset_manager If true, force the use of asset manager even if debug mode is on (useful to debug asset manager's problems) */
    public static $use_asset_manager;
    /** @var AdminTheme $instance The instance of theme object */
    private static $instance;

    /**
     * Get the list of admin theme, as an array containing each configuration object for each template
     * @return array the array of configuration object
     */
    static public function getAdminThemeList()
    {
        $sStandardTemplateRootDir  = Yii::app()->getConfig("styledir"); // The directory containing the default admin themes
        $sUserTemplateDir          = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'admintheme'; // The directory containing the user themes

        $aStandardThemeObjects     = self::getThemeList($sStandardTemplateRootDir); // array containing the configuration files of standard admin themes (styles/...)
        $aUserThemeObjects         = self::getThemeList($sUserTemplateDir); // array containing the configuration files of user admin themes (upload/admintheme/...)
        $aListOfThemeObjects       = array_merge($aStandardThemeObjects, $aUserThemeObjects);

        ksort($aListOfThemeObjects);
        return $aListOfThemeObjects;
    }

    /**
     * Set the Admin Theme :
     * - checks if the required template exists
     * - set the admin theme variables
     * - set the admin theme constants
     * - Register all the needed CSS/JS files
     * @return AdminTheme
     */
    public function setAdminTheme()
    {
        $sAdminThemeName           = getGlobalSetting('admintheme'); // We retrieve the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sStandardTemplateRootDir  = Yii::app()->getConfig("styledir"); // Path for the standard Admin Themes
        $sUserTemplateDir          = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'admintheme'; // Path for the user Admin Themes

        // Check if the required theme is a standard one
        if ($this->isStandardAdminTheme($sAdminThemeName)) {
            $sTemplateDir = $sStandardTemplateRootDir; // It's standard, so it will be in standard path
            $sTemplateUrl = Yii::app()->getConfig('styleurl').$sAdminThemeName; // Available via a standard URL
        } else {
            // If it's not a standard theme, we bet it's a user one.
            // In fact, it could also be a old 2.06 admin theme just aftet an update (it will then be caught as "non existent" in the next if statement")
            $sTemplateDir = $sUserTemplateDir;
            $sTemplateUrl = Yii::app()->getConfig('uploadurl').DIRECTORY_SEPARATOR.'admintheme'.DIRECTORY_SEPARATOR.$sAdminThemeName;
        }

        // If the theme directory doesn't exist, it can be that:
        // - user updated from 2.06 and still have old theme configurated in database
        // - user deleted a custom theme
        // In any case, we just set Sea Green as the template to use
        if (!is_dir($sTemplateDir.DIRECTORY_SEPARATOR.$sAdminThemeName)) {
            $sAdminThemeName   = 'Sea_Green';
            $sTemplateDir      = $sStandardTemplateRootDir;
            $sTemplateUrl      = Yii::app()->getConfig('styleurl').DIRECTORY_SEPARATOR.$sAdminThemeName;
            SettingGlobal::setSetting('admintheme', 'Sea_Green');
        }

        // Now that we are sure we have an existing template, we can set the variables of the AdminTheme
        $this->sTemplateUrl = $sTemplateUrl;
        $this->name         = $sAdminThemeName;
        $this->path         = $sTemplateDir.DIRECTORY_SEPARATOR.$this->name;

        // This is necessary because a lot of files still use "adminstyleurl".
        // TODO: replace everywhere the call to Yii::app()->getConfig('adminstyleurl) by $oAdminTheme->sTemplateUrl;
        Yii::app()->setConfig('adminstyleurl', $this->sTemplateUrl);


        //////////////////////
        // Config file loading

        $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        $sXMLConfigFile        = file_get_contents(realpath($this->path.'/config.xml')); // Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

        // Simple Xml is buggy on PHP < 5.4. The [ array -> json_encode -> json_decode ] workaround seems to be the most used one.
        // @see: http://php.net/manual/de/book.simplexml.php#105330 (top comment on PHP doc for simplexml)
        $this->config = json_decode(json_encode((array) simplexml_load_string($sXMLConfigFile), 1));

        // If developers want to test asset manager with debug mode on
        self::$use_asset_manager = isset($this->config->engine->use_asset_manager_in_debug_mode) ? ($this->config->engine->use_asset_manager_in_debug_mode == 'true') : false;

        $this->defineConstants(); // Define the (still) necessary constants
        $this->registerStylesAndScripts(); // Register all CSS and JS

        libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server

        return $this;
    }

    /**
     * Register all the styles and scripts of the current template.
     * Check if RTL is needed, use asset manager if needed.
     * This function is public because it appears that sometime, the package need to be register again in header (probably a cache problem)
     */
    public function registerStylesAndScripts()
    {
        // First we register the different needed packages

        // Bootstrap Registration
        // We don't want to use bootstrap extension's register functionality, to be able to set dependencies between packages
        // ie: to control load order setting 'depends' in our package
        // So, we take the usual Bootstrap extensions TbApi::register (called normally with  App()->bootstrap->register()) see: https://github.com/LimeSurvey/LimeSurvey/blob/master/application/extensions/bootstrap/components/TbApi.php#l162-l169
        // keep here the necessary  (registerMetaTag and registerAllScripts),
        // and move the rest to the bootstrap package.
        // NB: registerAllScripts could be replaced by js definition in package. If needed: not a problem to do it

        if (!Yii::app()->request->getQuery('isAjax', false)) {
            Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport'); // See: https://github.com/LimeSurvey/LimeSurvey/blob/master/application/extensions/bootstrap/components/TbApi.php#l108-l115
            App()->bootstrap->registerTooltipAndPopover(); // See : https://github.com/LimeSurvey/LimeSurvey/blob/master/application/extensions/bootstrap/components/TbApi.php#l153-l160
            App()->getClientScript()->registerScript('coreuser', '
           LS.globalUserId = "'.Yii::app()->user->id.'";', CClientScript::POS_HEAD);
            App()->getClientScript()->registerPackage('jquery'); // jquery
            App()->getClientScript()->registerPackage('jqueryui'); // jqueryui
            App()->getClientScript()->registerPackage('js-cookie'); // js-cookie
            App()->getClientScript()->registerPackage('fontawesome'); // fontawesome
            App()->getClientScript()->registerPackage('bootstrap-switch');
            App()->getClientScript()->registerPackage('bootstrap-select2');
            App()->getClientScript()->registerPackage('bootstrap-datetimepicker');
            App()->getClientScript()->registerPackage('font-roboto');
            App()->getClientScript()->registerPackage('font-icomoon');
            App()->getClientScript()->registerPackage('adminbasics'); // Combined scripts and style
            App()->getClientScript()->registerPackage('adminbasicjs');
            App()->getClientScript()->registerPackage('adminsidepanel'); // The new admin panel
            App()->getClientScript()->registerPackage('lstutorial'); // Tutorial scripts
            App()->getClientScript()->registerPackage('ckeditor'); //
            App()->getClientScript()->registerPackage('ckeditoradditions'); // CKEDITOR in a global sope
        }

        $aCssFiles = array();
        $aJsFiles = array();

        // Then we add the different CSS/JS files to load in arrays
        // It will check if it needs or not the RTL files
        // and it will add the directory prefix to the file name (css/ or js/ )
        // This last step is needed for the package (yii package use a single baseUrl / basePath for css and js files )

        // Shorter writing.
        $files = $this->config->files;

        // We check if RTL is needed
        if (getLanguageRTL(Yii::app()->language)) {
            if (!isset($files->rtl)
                || !isset($files->rtl->css)) {
                throw new CException("Invalid template configuration: No CSS files found for right-to-left languages");
            }

            if (is_array($files->rtl->css->filename)) {
                foreach ($files->rtl->css->filename as $cssfile) {
                    $aCssFiles[] = 'css/'.$cssfile; // add the 'css/' prefix to the RTL css files
                }
            } elseif (is_string($files->rtl->css->filename)) {
                $aCssFiles[] = 'css/'.$files->rtl->css->filename;
            }

            App()->getClientScript()->registerPackage('font-roboto');
            App()->getClientScript()->registerPackage('adminbasicsrtl');
            App()->getClientScript()->registerPackage('adminsidepanelrtl');

        } else {
            
            App()->getClientScript()->registerPackage('adminbasicsltr');
            App()->getClientScript()->registerPackage('adminsidepanelltr');
            // Non-RTL style
            if (is_array($files->css->filename)) {
                foreach ($files->css->filename as $cssfile) {
                    $aCssFiles[] = 'css/'.$cssfile; // add the 'css/' prefix to the css files
                }
            } elseif (is_string($files->css->filename)) {
                $aCssFiles[] = 'css/'.$files->css->filename;
            }
        }

        if (!empty($files->js->filename)) {
            if (is_array($files->js->filename)) {
                foreach ($files->js->filename as $jsfile) {
                    $aJsFiles[] = 'scripts/'.$jsfile; // add the 'js/' prefix to the js files
                }
            } elseif (is_string($files->js->filename)) {
                    $aJsFiles[] = 'scripts/'.$files->js->filename;
            }
        }


        $package = array();

        // We check if the asset manager should be use.
        // When defining the package with a base path (a directory on the file system), the asset manager is used
        // When defining the package with a base url, the file is directly registerd without the asset manager
        // See : http://www.yiiframework.com/doc/api/1.1/CClientScript#packages-detail
        if (!YII_DEBUG || self::$use_asset_manager || Yii::app()->getConfig('use_asset_manager')) {
            Yii::setPathOfAlias('admin.theme.path', $this->path);
            $package['basePath'] = 'admin.theme.path'; // add the base path to the package, so it will use the asset manager
        } else {
            $package['baseUrl'] = $this->sTemplateUrl; // add the base url to the package, so it will not use the asset manager
        }

        $package['css']     = $aCssFiles; // add the css files to the package
        $package['js']      = $aJsFiles; // add the js files to the package
        $package['depends'] = array('bootstrap');

        Yii::app()->clientScript->addPackage('admin-theme', $package); // add the package
        Yii::app()->clientScript->registerPackage('admin-theme'); // register the package
        Yii::app()->clientScript->registerPackage('moment'); // register moment for correct dateTime calculation

    }

    /**
     * Get instance of theme object.
     * Will instantiate the Admin Theme object first time it is called.
     * Please use this instead of global variable.
     * @return AdminTheme
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
            self::$instance->setAdminTheme();
        }
        return self::$instance;
    }

    /**
     * @return string[]
     */
    public static function getOtherAssets()
    {
        return array(
            // Extension assets
            'application/extensions/yiiwheels/assets',
            'application/extensions/yiiwheels/widgets/box/assets',
            'application/extensions/yiiwheels/widgets/grid/assets',
            'application/extensions/yiiwheels/widgets/formhelpers/assets',
            'application/extensions/yiiwheels/widgets/highcharts/assets',
            'application/extensions/yiiwheels/widgets/maskinput/assets',
            'application/extensions/yiiwheels/widgets/redactor/assets',
            'application/extensions/yiiwheels/widgets/switch/assets',
            'application/extensions/yiiwheels/widgets/fineuploader/assets',
            'application/extensions/yiiwheels/widgets/datetimepicker/assets',
            'application/extensions/yiiwheels/widgets/timeago/assets',
            'application/extensions/yiiwheels/widgets/sparklines/assets',
            'application/extensions/yiiwheels/widgets/datepicker/assets',
            'application/extensions/yiiwheels/widgets/multiselect/assets',
            'application/extensions/yiiwheels/widgets/gallery/assets',
            'application/extensions/yiiwheels/widgets/select2/assets',
            'application/extensions/yiiwheels/widgets/ace/assets',
            'application/extensions/yiiwheels/widgets/modal/assets',
            'application/extensions/yiiwheels/widgets/maskmoney/assets',
            'application/extensions/yiiwheels/widgets/rangeslider/assets',
            'application/extensions/yiiwheels/widgets/fileupload/assets',
            'application/extensions/yiiwheels/widgets/typeahead/assets',
            'application/extensions/yiiwheels/widgets/timepicker/assets',
            'application/extensions/yiiwheels/widgets/html5editor/assets',
            'application/extensions/yiiwheels/widgets/daterangepicker/assets',
            'application/extensions/bootstrap/assets',
            'application/extensions/LimeScript/assets',
            'application/extensions/SettingsWidget/assets',
            'application/extensions/FlashMessage/assets',
            'application/extensions/admin/survey/question/PositionWidget/assets',
            'application/extensions/admin/grid/MassiveActionsWidget/assets',
            'application/extensions/admin/survey/question/PositionWidget/assets',
            //'application/extensions/bootstrap/', we'll touch all the subdirectories of extensions

            // Third party assets
            'third_party/jquery-tablesorter/tests/assets',
            'third_party/jquery-tablesorter/docs/assets',
        );
    }

    /**
     * Return an array containing the configuration object of all templates in a given directory
     *
     * @param string $sDir          the directory to scan
     * @return array                the array of object
     */
    static private function getThemeList($sDir)
    {
        $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        $aListOfFiles = array();
        $oAdminTheme = new AdminTheme;
        if ($sDir && $pHandle = opendir($sDir)) {
            while (false !== ($file = readdir($pHandle))) {
                if (is_dir($sDir.DIRECTORY_SEPARATOR.$file) && is_file($sDir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'config.xml')) {
                    $sXMLConfigFile = file_get_contents(realpath($sDir.DIRECTORY_SEPARATOR.$file.'/config.xml')); // Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

                    // Simple Xml is buggy on PHP < 5.4. The [ array -> json_encode -> json_decode ] workaround seems to be the most used one.
                    // @see: http://php.net/manual/de/book.simplexml.php#105330 (top comment on PHP doc for simplexml)
                    $oTemplateConfig = json_decode(json_encode((array) simplexml_load_string($sXMLConfigFile), 1));
                    if ($oAdminTheme->isStandardAdminTheme($file)) {
                        $previewUrl = Yii::app()->getConfig('styleurl').$file;
                    } else {
                        $previewUrl = Yii::app()->getConfig('uploadurl').DIRECTORY_SEPARATOR.'admintheme'.DIRECTORY_SEPARATOR.$file;
                    }
                    $oTemplateConfig->path    = $file;
                    $oTemplateConfig->preview = '<img src="'.$previewUrl.'/preview.png" alt="admin theme preview" height="200" class="img-thumbnail" />';
                    $aListOfFiles[$file] = $oTemplateConfig;
                }
            }
            closedir($pHandle);
        }
        libxml_disable_entity_loader($bOldEntityLoaderState);
        return $aListOfFiles;
    }


    /**
     * Few constants depending on Template
     */
    private function defineConstants()
    {
        // Define images url
        if (!YII_DEBUG || self::$use_asset_manager || Yii::app()->getConfig('use_asset_manager')) {
            define('LOGO_URL', App()->getAssetManager()->publish($this->path.'/images/logo.png'));
            define('LOGO_ICON_URL', App()->getAssetManager()->publish($this->path.'/images/logo_icon.png'));
        } else {
            define('LOGO_URL', $this->sTemplateUrl.'/images/logo.png');
            define('LOGO_ICON_URL', $this->sTemplateUrl.'/images/logo_icon.png');
        }

        // Define presentation text on welcome page
        if (isset($this->config->metadata->presentation) && $this->config->metadata->presentation) {
            define('PRESENTATION', $this->config->metadata->presentation);
        } else {
            define('PRESENTATION', gT('This is the LimeSurvey admin interface. Start to build your survey from here.'));
        }
    }

    /**
     * Use to check if admin theme is standard
     *
     * @var string $sAdminThemeName     the name of the template
     * @param string $sAdminThemeName
     * @return boolean                  return true if it's a standard template, else false
     */
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
