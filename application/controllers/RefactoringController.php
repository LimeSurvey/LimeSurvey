<?php


class RefactoringController extends LSYii_Controller
{

    //##################################                 TESTING ONLY #################################################


    public function actionIndex(){

      $survey = Survey::model()->findAll(); // works ...
        $boxes = Box::model()->findAll(); //this works ...

        $model = [];
        $model['name'] = 'someName';
        $model['id'] = 15;

        $this->render('index', array(
            'model' => $model,
           'survey' => $survey
        ));
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
