<?php


namespace ls\controllers;


use ls\models\LabelSet;

class LabelsController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new \CActiveDataProvider(LabelSet::class)
        ]);
    }

    public function actionCreate()
    {
        $model = new LabelSet();
        if (App()->request->isPostRequest) {
            $model->setAttributes(App()->request->getPost(\CHtml::modelName($model)));

            if ($model->save()) {
                App()->user->setFlash('success', gT("Label set created!"));
                return $this->redirect(['labels/update', 'id' => $model->lid]);
            }
        }
        $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = LabelSet::model()->findByPk($id);
        if (isset($model)) {
            if (App()->request->isPutRequest) {
                $model->setAttributes(App()->request->getPost(\CHtml::modelName($model)));

                if ($model->save()) {
                    App()->user->setFlash('success', gT("Label set updated!"));
                    return $this->redirect(['labels/update', 'id' => $model->lid]);
                }
            }
            $this->render('update', ['model' => $model]);

        }
    }


//    public funct
}