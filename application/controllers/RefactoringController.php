<?php


class RefactoringController extends LSYii_Controller
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
    //##################################                 TESTING ONLY #################################################


    public function actionIndex(){

        if(!Yii::app()->user->isGuest) {
            $survey = Survey::model()->findAll(); // works ...
            $boxes = Box::model()->findAll(); //this works ...

            $model = [];
            $model['name'] = 'someName';
            $model['id'] = 15;

           return $this->render('index', array(
                'model' => $model,
                'survey' => $survey
            ));
        }
            $this->redirect(array('/admin/authentication/sa/login'));

    }

    public function actionListSurveys(){
        Yii::app()->loadHelper('surveytranslator');
        $aData = array();
        $aData['issuperadmin'] = Permission::model()->hasGlobalPermission('superadmin', 'read');

        /*
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }*/


        $aData['model'] = new Survey('search'); // todo get it as var not inside array ...
        $aData['groupModel'] = new SurveysGroups('search');
        $aData['fullpagebar']['button']['newsurvey'] = true;

        $this->render('listsurveys', [
           'aData' => $aData,
            'model' => $aData['model'],
            'groupModel' => $aData['groupModel']
        ]);

      //  $this->_renderWrappedTemplate('survey', 'listSurveys_view', $aData);
    }

}
