<?php
namespace ls\controllers;
use \Yii;
class QuestionsController extends Controller 
{
    public $layout = 'survey';
    public function actionView($id) {
        $this->question = $this->loadModel($id);
        $this->survey = $this->question->survey;
        $this->group = $this->question->group;
        
        $this->render('view', ['question' => $this->question]);
    }

    public function actionUpdate($id) {
        $this->question = $question = $this->loadModel($id);
        $this->survey = $this->question->survey;
        $this->group = $this->question->group;
        if (App()->request->isPutRequest) {
            // Update the question from data.
            $error = false;
            $answers = [];
            if ($this->question->hasAnswers) {

//                echo '<pre>';
                // Remove all answers.
                // Create new ones.
                foreach(App()->request->getParam('Answer') as $data) {
                    $answer = new \Answer();
                    $answer->question_id = $this->question->qid;
                    $answer->setAttributes($data);
                    $answers[] = $answer;
                    $error = $error || !$answer->validate();
                }
                $this->question->answers = $answers;
            }


            $this->question->setAttributes(App()->request->getParam(\CHtml::modelName($question)));
            if (
                // Validation error in dependent models
                !$error
                // Validate and save question.
                && $this->question->save()
                // Remove old answers. Use individual delete to handle removal of dependent records.
                && array_reduce(\Answer::model()->findAllByAttributes(['question_id' => $question->qid]), function($carry, \Answer $answer) {
                    return $carry && $answer->delete();
                }, true)
                // Save new answers.
                && array_reduce($answers, function($carry, \Answer $answer) {
                return $carry && $answer->save();
                }, true)) {
                App()->user->setFlash('success', "Question updated.");
            } else {
                App()->user->setFlash('error', "Question could not be updated.");
            }
        }

        $this->render('update', ['question' => $this->question, 'post' => $_POST, 'questionnames' => $this->question->translations]);
    }

    public function actionCreate($groupId) {
        /**
         * @todo Switch to findByPk after language has been removed from group table.
         */
        $group = \QuestionGroup::model()->findByAttributes(['gid' => $groupId]);
        $this->survey = $group->survey;
        if (!isset($this->survey)) {
            throw new \CHttpException(404, "Survey not found.");
        } elseif ($this->survey->isActive) {
            throw new \CHttpException(421, "Cannot add questions to active survey.");
        }
        $question = new \Question();
        $question->sid = $group->sid;
        $question->gid = $group->gid;
        if (App()->request->isPostRequest) {
            $question->setAttributes(App()->request->getPost('Question'));
            if ($question->save()) {
                $this->redirect(['questions/update', 'id' => $question->primaryKey]);
            }
        } else {
            $lastTitle = array_values($question->survey->questions)[count($question->survey->questions) - 1]->title;
            if (isset($lastTitle) && preg_match('/^(.*?)(\d+)$/', $lastTitle, $matches)) {
                $question->title = $matches[1] . ($matches[2] + 1);
            }
        }
        $this->render('create', ['question' => $question]);

    }


    /**
     * Delete a question.
     * @todo Access check
     * @todo Method check, delete should always done via HTTP DELETE or DELETE over POST.
     * @param $id
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        if (isset($model) && $model->delete()) {
            App()->user->setFlash('success', "Question deleted.");
        }
        if (isset($model)) {
            $this->redirect(["surveys/update", "id" => $model->sid]);
        } else {
            $this->redirect('surveys/index');
        }
    }
    /**
     * @param int $id
     * @return \Question
     */
    protected function loadModel($id) {
        return \Question::model()->findByAttributes([
            'qid' => $id
        ]);
    }
}

