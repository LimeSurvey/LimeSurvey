<?php
namespace ls\controllers;
use ls\models\forms\SubQuestions;
use ls\models\questions\SubQuestion;
use \Yii;
class QuestionsController extends Controller 
{
    public $layout = 'survey';
    public function actionView($id) {
        $this->menus['question'] = $question = $this->loadModel($id);
        $this->menus['group'] = $question->group;
        $this->menus['survey'] = $question->survey;

        
        $this->render('view', ['question' => $question]);
    }


    /**
     * Update happens inside a transaction so we can remove answers / subquestions and cancel if needed.
     * @param $id
     */

    public function actionUpdate($id) {
        $this->menus['question'] = $question = $this->loadModel($id);
        $this->menus['group'] = $question->group;
        $this->menus['survey'] = $question->survey;


        if (App()->request->isPutRequest) {
            $transaction = App()->db->beginTransaction();
            $error = false;
            $answers = [];
            if ($question->hasAnswers && App()->request->getParam('Answer', false) !== false) {

                // Remove all answers.
                array_map(function (\Answer $answer) {
                    return $answer->delete();
                }, \Answer::model()->findAllByAttributes(['question_id' => $question->qid]));
                // Create new ones.
                $codes = [];
                foreach(App()->request->getParam('Answer') as $i => $data) {
                    $answer = new \Answer();
                    $answer->question_id = $question->qid;
                    $answer->setAttributes($data);
                    $answer->sortorder = $i;
                    $answers[] = $answer;
                    $error = $error || !$answer->validate();
                    /**
                     * @todo Find a better solution for this manual validation.
                     */
                    if (isset($codes[$answer->code])) {
                        App()->user->setFlash('danger', gT("Error: You are trying to use duplicate answer codes."));
                        $error = true;
                    } else {
                        $codes[$answer->code] = true;
                    }
                }
                $question->answers = $answers;
            }
            $subQuestions = [];
            if ($question->hasSubQuestions && App()->request->getParam(\CHtml::modelName(SubQuestion::class), false) !== false) {

                // Remove all subquestions.
                array_map(function (\Question $question) {
                    return $question->delete();
                }, \Question::model()->findAllByAttributes(['parent_qid' => $question->qid]));

                // Create new ones.
                $codes = [];
                foreach(App()->request->getParam(\CHtml::modelName(SubQuestion::class)) as $i => $data) {
                    $subQuestion = new SubQuestion();
                    $subQuestion->parent_qid = $question->qid;
                    $subQuestion->gid = $question->gid;
                    $subQuestion->sid = $question->sid;
                    $subQuestion->setAttributes($data);
                    $subQuestion->question_order = $i;
                    $subQuestions[] = $subQuestion;
                    $error = $error || !$subQuestion->validate();

                    /**
                     * @todo Find a better solution for this manual validation.
                     */
                    if (isset($codes[$subQuestion->title])) {
                        App()->user->setFlash('danger', gT("Error: You are trying to use duplicate question codes."));
                        die('no');
                        $error = true;
                    } else {
                        $codes[$subQuestion->title] = true;
                    }
                }
                $question->subQuestions = $subQuestions;
            }

            if (!$error) {
                $question->setAttributes(App()->request->getParam(\CHtml::modelName($question)));
                if (// Validate and save question.
                    $question->save()
                    // Save new answers.
                    && array_reduce($answers, function ($carry, \Answer $answer) {
                        return $carry && $answer->save();
                    }, true)
                    // Save new subquestions.
                    && array_reduce($subQuestions, function ($carry, \Question $subQuestion) {
                        return $carry && $subQuestion->save();
                    }, true)
                ) {
                    $transaction->commit();
                    App()->user->setFlash('success', "Question updated.");
                } else {
                    $transaction->rollback();
                    App()->user->setFlash('danger', "Question could not be updated.");

                }
            } elseif (count(App()->user->getFlashes(false)) == 0) {
                App()->user->setFlash('danger', "Unknown error.");
            }
        }

        $this->render('update', ['question' => $question, 'questionnames' => $question->translations]);
    }

    public function actionCreate($groupId) {
        /**
         * @todo Switch to findByPk after language has been removed from group table.
         */
        $group = \QuestionGroup::model()->findByPk($groupId);
        $this->menus['survey'] = $group->survey;
        if (!isset($group->survey)) {
            throw new \CHttpException(404, "Survey not found.");
        } elseif ($group->survey->isActive) {
            throw new \CHttpException(421, "Cannot add questions to active survey.");
        }
        $question = new \Question();
        $question->sid = $group->sid;
        $question->gid = $group->primaryKey;
        if (App()->request->isPostRequest) {
            $question->setAttributes(App()->request->getPost('Question'));
            if ($question->save()) {
                $this->redirect(['questions/update', 'id' => $question->primaryKey]);
            }
        } else {

            $lastTitle = ([] != $values = array_values($question->survey->questions)) ? $values[count($question->survey->questions) - 1]->title : "q0";
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
        $result = \Question::model()->findByAttributes([
            'qid' => $id
        ]);
        if (!isset($result)) {
            throw new \CHttpException(404, gT("Question not found."));
        }
        return $result;
    }
}

