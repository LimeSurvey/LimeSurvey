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
        App()->getClientScript()->registerScriptFile('/application/views/failedEmail/javascript/failedEmail.js', LSYii_ClientScript::POS_BEGIN);

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
        if (!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            return;
        }
        $preserveResend = App()->request->getParam('preserveResend');
        $selectedItems = json_decode(App()->request->getParam('sItems'));
        if (!empty($selectedItems)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, email_type, recipient';
            $criteria->addCondition('surveyid', $surveyId);
            $criteria->addInCondition('id', $selectedItems);
            $failedEmails = FailedEmail::model()->findAll($criteria);
            if (isset($failedEmails)) {
                foreach ($failedEmails as $failedEmail) {
                    $emailsByType[$failedEmail->email_type][$failedEmail->id] = $failedEmail->recipient;
                }
                if (isset($emailsByType)) {
                    sendSubmitNotifications($surveyId, $emailsByType, $preserveResend);
                    // TODO: return message
                }
            }
            // TODO: return message
        }
        // TODO: return message
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
        $selectedItems = json_decode(App()->request->getParam('sItems'));
        if (!empty($selectedItems)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, email_type, recipient';
            $criteria->addCondition('surveyid', $surveyId);
            $criteria->addInCondition('id', $selectedItems);
            $failedEmails = new FailedEmail;
            $failedEmails->deleteAll($criteria);
            // TODO: return message
        }
        // TODO: return message
    }

    public function actionModalContent()
    {
        $contentFile = App()->request->getParam('contentFile');
        $id = App()->request->getParam('id');
        return App()->getController()->renderPartial('/failedEmail/partials/modal/' . $contentFile, ['id' => $id]);
    }
}
