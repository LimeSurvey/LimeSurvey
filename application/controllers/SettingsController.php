<?php

namespace ls\controllers;
use ls\models\forms\Settings;
use SamIT\Form\FormHelper;

class SettingsController extends Controller {

    public $layout = 'main';
    public function actionIndex()
    {
        $settings = new Settings();
        return $this->render('index', ['settings' => $settings]);
    }

    public function actionUpdate()
    {
        $settings = new Settings();
        if (App()->request->isPutRequest) {
            $settings->setAttributes(App()->request->getParam(\CHtml::modelName($settings)));
            if ($settings->save()) {
                App()->user->setFlash('success', gT('Settings updated.'));
                $this->redirect(['settings/index']);
            }
            return $this->render('index', ['settings' => $settings]);
        }
    }

}
