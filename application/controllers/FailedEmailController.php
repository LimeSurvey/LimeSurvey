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
            'failedEmailModel' => $failedEmailModel,
            'pageSizeTokenView' => $pageSizeTokenView,
            'massiveAction' => $massiveAction
        ]);
	}
}