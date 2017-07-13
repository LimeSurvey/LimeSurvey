<?php

class SurveymenuEntryController extends Survey_Common_Action
{
	 /**
     *
     * @access public
     * @return void
     */
    public function index()
    {
        $this->getController()->redirect(array('admin/menuentries/sa/view'));
    }

	public function view()
	{
		//$this->checkPermission();

        $data = array();
        $data['model'] = SurveymenuEntries::model();
		$data['user'] = Yii::app()->session['loginID'];
        $this->_renderWrappedTemplate(null, array('surveymenu_entries/index'), $data);
	}


	public function getsurveymenuentryform($menuentryid=null){
		$menuentryid = Yii::app()->request->getParam('menuentryid', null);
		if($menuentryid != null)
		{
        	$model = SurveymenuEntries::model()->findByPk(((int) $menuentryid));
		} else 
		{
        	$model = SurveymenuEntries::model();
		}
		$user = Yii::app()->session['loginID'];
		return Yii::app()->getController()->renderPartial('/admin/surveymenu_entries/_form', array('model'=>$model, 'user'=>$user));
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