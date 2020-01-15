<?php


class RefactoringController extends CController
{

    public function actionIndex(){

        //$survey = Survey::model()->findAll();  todo make this work ...


        $model = [];
        $model['name'] = 'someName';
        $model['id'] = 15;

        $this->render('index', array(
            'model' => $model,
           // 'survey' => $survey
        ));
    }
}
