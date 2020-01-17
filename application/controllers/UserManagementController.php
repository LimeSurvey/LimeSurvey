<?php


class UserManagementController extends LSYii_Controller
{

    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions'=>array('login'),
                'users'=>array('*'), //everybody
            ),
            array(
                'allow',
                'actions'=>array('index','listSurveys'),
                'users'=>array('@'), //only login users
            ),
            array('deny'),
        );
    }

    public function actionIndex(){
        if (!Permission::model()->hasGlobalPermission('users', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', Yii::app()->request->getParam('pageSize'));
        }
        App()->getClientScript()->registerPackage('usermanagement');
        App()->getClientScript()->registerPackage('bootstrap-select2');

        $aData = [];
        $model = new User('search');
        $model->setAttributes(Yii::app()->getRequest()->getParam('User'), false);
        $aData['model'] = $model;

        $aData['columnDefinition'] = $model->managementColums;
        $aData['pageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['formUrl'] = App()->createUrl('usermanagement/index');

        $aData['massiveAction'] = App()->getController()->renderPartial(
            'usermanagement/massiveAction/_selector',
            [],
            true,
            false
        );

        return $this->render('index', [
            'aData' => $aData,
            'model' => $aData['model'],
            'columnDefinition' => $aData['columnDefinition'],
            'pageSize' => $aData['pageSize'],
            'formUrl' => $aData['formUrl'],
            'massiveAction' => $aData['massiveAction'],
        ]);
        //$this->_renderWrappedTemplate('usermanagement', 'view', $aData);
    }


}
