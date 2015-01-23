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
    */
    public static function templateNameFilter($sTemplateName)
    {
        $usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
        $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
        $sDefaultTemplate = Yii::app()->getConfig('defaulttemplate');// !empty ?
        if (!empty($sTemplateName) && is_dir("$usertemplaterootdir/{$sTemplateName}/"))// Maybe better validate is_file("$usertemplaterootdir/{$sTemplateName}/startpage.pstpl")
        {
            return $sTemplateName;
        }
        elseif (!empty($sTemplateName) && is_dir("$standardtemplaterootdir/{$sTemplateName}/"))
        {
            return $sTemplateName;
        }
        elseif (is_dir("$standardtemplaterootdir/{$sDefaultTemplate}/"))
        {
            return $sDefaultTemplate;
        }
        elseif (is_dir("$usertemplaterootdir/{$sDefaultTemplate}/"))
        {
            return $sDefaultTemplate;
        }
        return 'default';

    }
}
