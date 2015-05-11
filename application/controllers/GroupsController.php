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

    /**
     * Delete a group.
     * @todo Access check
     * @todo Method check, delete should alwasy done via HTTP DELETE or DELETE over POST.
     * @param $id
     */
    public function actionDelete($id)
    {
        $group = $this->loadModel($id);
        if (isset($group) && $group->questionCount == 0 && $group->delete()) {
            App()->user->setFlash('success', "Group deleted.");
        }
        if (isset($group)) {
            $this->redirect(["surveys/update", "id" => $group->sid]);
        } else {
            $this->redirect('surveys/index');
        }
    }
    
    protected function loadModel($id)
    {
        return \QuestionGroup::model()->findByPk($id);
    }
}