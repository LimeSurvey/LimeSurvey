<?php

class SurveyPermissionsController extends LSBaseController
{
    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['index'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    public function actionIndex(){

    }
}
