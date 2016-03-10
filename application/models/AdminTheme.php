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

    /**
     * Return the necessary datas to load the admin theme
     */
    public function setAdminTheme()
    {
        // We retrieve the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sAdminThemeName = Yii::app()->getConfig('admintheme');
        $sAdminTemplateRootDir=Yii::app()->getConfig("styledir");

        // If the template doesn't exist, set to Default
        $sAdminThemeName = ($this->isStandardAdminTheme($sAdminThemeName))?$sAdminThemeName:'default';

        // If the required admin theme doesn't exist, Sea_Green will be used
        // TODO : check also for upload directory
        $this->name = (is_dir($sAdminTemplateRootDir.DIRECTORY_SEPARATOR.$sAdminThemeName))?$sAdminThemeName:'Sea_Green';

        // The path of the template files eg : /var/www/limesurvey/styles/Sea_Green
        // TODO : add the upload directory for user template
        $this->path = $sAdminTemplateRootDir.DIRECTORY_SEPARATOR.$this->name;

        // The template configuration.
        $this->config = simplexml_load_file($this->path.'/config.xml');
        return $this;
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
