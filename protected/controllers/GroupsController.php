<?php
namespace ls\controllers;
use \Yii;
class GroupsController extends Controller {
    public $layout = 'survey';
    public function actionView($id) {
        $this->menus['group'] = $group = $this->loadModel($id);
        $this->menus['survey'] = $group->survey;
        return $this->render('view', ['group' => $group]);
    }

    /**
     * Delete a group.
     * @todo Access check
     * @todo Method check, delete should always done via HTTP DELETE or DELETE over POST.
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

    /**
     * @todo Access check
     * @param int $surveyId
     * @throws \CHttpException
     *
     */
    public function actionCreate($surveyId) {
        /**
         * @todo Switch to findByPk after language has been removed from group table.
         */
        $this->menus['survey'] = $survey = \ls\models\Survey::model()->findByPk($surveyId);

        if (!isset($survey)) {
            throw new \CHttpException(404, "Survey not found");
        } elseif ($survey->isActive) {
            throw new \CHttpException(421, "Cannot add groups to active survey.");
        }
        $group = new \ls\models\QuestionGroup();
        $group->sid = $survey->primaryKey;
        if (App()->request->isPostRequest) {
            $group->setAttributes(App()->request->getPost(\CHtml::modelName($group)));

            if ($group->save()) {
                return $this->redirect(['groups/update', 'id' => $group->primaryKey]);
            }
        } else {
            $lastTitle = ([] != $values = array_values($group->survey->groups)) ? $values[count($group->survey->groups) - 1]->group_name : "g0";
            if (isset($lastTitle) && preg_match('/^(.*?)(\d+)$/', $lastTitle, $matches)) {
                $group->group_name = $matches[1] . ($matches[2] + 1);
            }
        }
        $this->render('create', ['group' => $group]);

    }

    public function actionUpdate($id) {
        $this->menus['group'] = $group = $this->loadModel($id);
        $this->menus['survey'] = $group->survey;
        /**
         * @todo Add access check.
         */
        if (App()->request->isPutRequest) {
            $group->setAttributes(App()->request->getPost(\TbHtml::modelName($group)));
            if ($group->save()) {
                App()->user->setFlash('success', gT("Group updated"));
            }
        }
        $this->render('update', ['group' => $group]);
    }
    public function loadModel($id)
    {
        return \ls\models\QuestionGroup::model()->findByPk($id);
    }
}