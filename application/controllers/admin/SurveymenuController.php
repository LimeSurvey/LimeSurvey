<?php

class SurveymenuController extends Survey_Common_Action
{
	public function index()
    {
        $this->getController()->redirect(array('admin/menus/sa/view'));
    }

	public function view()
	{
		//$this->checkPermission();

        $data = array();
        $data['model'] = Surveymenu::model();

        $this->_renderWrappedTemplate(null, array('surveymenu/index'), $data);
	}

	public function getsurveymenuform($menuid=null){
		$menuid = Yii::app()->request->getParam('menuid', null);
		if($menuid != null)
		{
        	$model = Surveymenu::model()->findByPk(((int) $menuid));
		} else 
		{
        	$model = Surveymenu::model();
		}
		$user = Yii::app()->session['loginID'];
		return Yii::app()->getController()->renderPartial('/admin/surveymenu/_form', array('model'=>$model, 'user'=>$user));
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}