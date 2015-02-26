<?php
namespace ls\controllers;
use \Yii;
class GroupsController extends Controller {
    public $layout = 'survey';
    public function actionView($id) {
        $this->group = $this->loadModel($id);
        $this->survey = $this->group->survey;
        return $this->render('view', ['group' => $this->group]);
    }
    
    protected function loadModel($id) {
        return \QuestionGroup::model()->findByAttributes([
            'gid' => $id,
            'language' => App()->language
        ]);
    }
}