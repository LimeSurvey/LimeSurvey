<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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

/**
* podes
*
* @package IncubatorSurvey
* @author  Rahmat Awaludin (rahmat.awaludin@gmail.com)
* @copyright 2012
* @version $Id: podes.php 
* @access public
*/
class Podes extends Survey_Common_Action
{
    /**
    * Initiates the survey action, checks for superadmin permission
    *
    * @access public
    * @param CController $controller
    * @param string $id
    * @return void
    */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);
    }
    
    /**
    * This function show form to select location for podes    
    */
    function index()
    {
        /* if (!hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            $this->getController()->error('No permission'); */                      		
		$model=new PotensiForm;

		// uncomment the following code to enable ajax-based validation
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='potensi-form-Index-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['PotensiForm']))
		{
                        echo print_r($_POST['PotensiForm']);
			/* $model->attributes=$_POST['PotensiForm'];
			if($model->validate())
			{
				//echo $model['provinsiid'];
				$this->render('view',array(
					'id'=>$model['desaid'],
					'kat3'=>$model['kat3'],
					'kat4'=>$model['kat4'],
					'kat5'=>$model['kat5'],
					'kat6'=>$model['kat6'],
					'kat7'=>$model['kat7'],
					'kat8'=>$model['kat8'],
					'kat9'=>$model['kat9'],
					'kat10'=>$model['kat10'],
					'kat12'=>$model['kat12'],
				));
				return;
			} */
		}		
		
                $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'podes.js');
		//$aData['display']['menu_bars']['browse'] = "Quick statistics";
                
                $aData['model'] = $model;
        $this->_renderWrappedTemplate('podes', 'index_view', $aData);
    }
    
    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'podes', $aViewUrls = array(), $aData = array())
    {
        //$this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
    
    /**
    * Fungsi untuk mengambil data kabupaten dari Ajax berdasarkan ID provinsi
    */
    function getkabupaten(){
        /*if(!Yii::app()->request->isAjaxRequest)
               throw new CHttpException(404); */

        $data=Kabupaten::model()->findAll(
                'provinsiid=:provinsiid',
                array(':provinsiid'=>(int)$_POST['provinsiid'])                
        );

        $data=CHtml::listData($data, 'id', 'nama');        
        foreach ($data as $value=>$nama)
        {
            echo CHtml::tag('option',array('value'=>$value), CHtml::encode($nama), true);                        
            //echo '< option value="'.$value.'" selected="'.$nama.'">< /option>';
            
        }
    }
}