<?php

class SurveymenuEntryController extends Survey_Common_Action
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

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
		$filterAndSearch = Yii::app()->request->getPost('SurveymenuEntries', []);
        $data['model'] = SurveymenuEntries::model();
		$data['model']->setAttributes($filterAndSearch);
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
        	$model = new SurveymenuEntries();
		}
		$user = Yii::app()->session['loginID'];
		return Yii::app()->getController()->renderPartial('/admin/surveymenu_entries/_form', array('model'=>$model, 'user'=>$user));
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function create()
	{
		$model=new SurveymenuEntries;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SurveymenuEntries']))
		{
			$model->attributes=$_POST['SurveymenuEntries'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function update($id)
	{
		if($id != 0)
			$model = $this->loadModel($id);
		else 
			$model = new SurveymenuEntries();
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		
		$success = false;
		if(Yii::app()->request->isPostRequest)
		{
			$aSurveymenuEntry = Yii::app()->request->getPost('SurveymenuEntries', []);

			$aSurveymenuEntry['changed_at'] = date('Y-m-d H:i:s');
			$aSurveymenuEntry['created_at'] = date('Y-m-d H:i:s');
			$aSurveymenuEntry['menu_id'] = (int) $aSurveymenuEntry['menu_id'];
			$model->setAttributes($aSurveymenuEntry);
			if($model->save()){
				$model->id = $model->getPrimaryKey();
				$success = true;
				SurveymenuEntries::reorderMenu($model->menu_id);
			}
		}

		return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success'=> $success,
					'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
					'debug' => [$model,$aSurveymenuEntry, $_POST],
					'debugErrors' => $model->getErrors(),
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    )
                ]
            ),
            false,
            false
        );
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function delete()
	{
		if( Yii::app()->request->isPostRequest )
		{
			$menuEntryid = Yii::app()->request->getPost('menuEntryid', 0);
			$success = false;
			$model = $this->loadModel($menuEntryid);
			$success = $model->delete();

			return Yii::app()->getController()->renderPartial(
				'/admin/super/_renderJson',
				array(
					'data' => [
						'success'=> $success,
						'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
						'debug' => [$model, $_POST],
						'debugErrors' => $model->getErrors(),
						'settings' => array(
							'extrasettings' => false,
							'parseHTML' => false,
						)
					]
				),
				false,
				false
			);
		}
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SurveymenuEntries the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SurveymenuEntries::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SurveymenuEntries $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='surveymenu-entries-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
