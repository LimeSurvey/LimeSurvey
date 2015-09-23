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

class Template extends ActiveRecord
{
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
    * Filter the template name : test if template if exist
    *
    * @param string $name
    * @return boolean True if the name is valid false otherwise.
    */
    public static function templateNameFilter($name)
    {
        /* Validate it's a real dir included in template allowed dir
        *  Alternative : use realpath("$dir/$sTemplateName")=="$dir/$sTemplateName" and is_dir
        */
        return in_array($name,self::getTemplateList());
    }

    /**
    * Get the template path for any template : test if template if exist
    *
    * @param string $name
    * @return string Path of the template files.
    */
    public static function getTemplatePath($name)
    {
        if (!self::templateNameFilter($name)) {
            throw new \Exception("Invalid template name $name");
        }

        if (self::isStandardTemplate($name)) {
            return Yii::getPathOfAlias("coreTemplates.$name");
        }
        else
        {
            return Yii::getPathOfAlias("userTemplates.$name");
        }
    }

    /**
    * This function returns the complete URL path to a given template name
    *
    * @param string $name
    * @return string template url
    */
    public static function getTemplateURL($name)
    {
        if (self::isStandardTemplate($name))
        {
//            vd(App()->getBaseUrl(true));
//            vdd(str_replace(Yii::getPathOfAlias('webroot'), App()->baseUrl, realpath(Yii::getPathOfAlias("coreTemplates.$name"))));
            return str_replace(Yii::getPathOfAlias('webroot'), App()->baseUrl, realpath(Yii::getPathOfAlias("coreTemplates.$name")));
        }
        else
        {
            return str_replace(Yii::getPathOfAlias('webroot'), App()->baseUrl, realpath(Yii::getPathOfAlias("userTemplates.$name")));
        }
    }

    public static function getTemplateList()
    {
        $result = array_merge(self::coreTemplates(), array_map(function(self $template) {
            return basename($template->folder);
        }, self::model()->findAll()));

        sort($result);
        return $result;
    }

    /**
    * isStandardTemplate returns true if a template is a standard template
    * This function does not check if a template actually exists
    *
    * @param string $template template name to look for
    * @return bool True if standard template, otherwise false
    */
    public static function isStandardTemplate($template)
    {
        return in_array($template, self::coreTemplates());
    }

    public static function coreTemplates() {
        return [
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
        ];
    }
}
