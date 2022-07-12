<?php

class FailedEmailController extends LSBaseController
{
    /**
     * this is part of renderWrappedTemplate implement in old responses.php
     *
     * @param string $view
     * @return bool
     */
    public function beforeRender($view)
    {
        $surveyId = (int)App()->request->getParam('surveyid');
        $this->aData['surveyid'] = $surveyId;
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        $this->layout = 'layout_questioneditor';

        return parent::beforeRender($view);
    }

    /**
     * @throws CHttpException
     */
    public function actionIndex(): void
    {
        $surveyId = sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }

        $failedEmailModel = FailedEmail::model();
        $pageSizeTokenView = App()->user->getState('pageSizeTokenView', App()->params['defaultPageSize']);
        $massiveAction = App()->getController()->renderPartial('/failedEmail/partials/massive_action_selector', ['surveyId' => $surveyId], true);

        $this->render('failedEmail_index', [
            'failedEmailModel'  => $failedEmailModel,
            'pageSizeTokenView' => $pageSizeTokenView,
            'massiveAction'     => $massiveAction
        ]);
    }

    /**
     * @throws CHttpException
     */
    public function actionResend(): void
    {
        $surveyId = sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }
        if (Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            return;
        }

        $failedEmailModel = FailedEmail::model()->findAllByAttributes(['email_type', 'recipient'], 'surveyid = :surveyid', [':surveyid' => $surveyId]);
        sendSubmitNotifications($surveyId, $failedEmailModel);
    }

    /**
     * @throws CHttpException
     */
    public function actionDelete(): void
    {
        $surveyId = sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }
        if (!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            return;
        }


    }
}
