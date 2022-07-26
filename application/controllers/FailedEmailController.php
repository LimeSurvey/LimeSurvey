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
        $permissions = [
            'update' => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update'),
            'delete' => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'delete'),
            'read'   => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'read')
        ];
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }
        if (!$permissions['read']) {
            App()->user->setFlash('error', gT("You do not have permission to access this page."));
            $this->redirect(['surveyAdministration/view', 'surveyid' => $surveyId]);
        }

        App()->getClientScript()->registerScriptFile('/application/views/failedEmail/javascript/failedEmail.js', LSYii_ClientScript::POS_BEGIN);
        $failedEmailModel = FailedEmail::model();
        $failedEmailModel->setAttributes(App()->getRequest()->getParam('FailedEmail'), false);
        $pageSize = App()->request->getParam('pageSize') ?? App()->user->getState('pageSize', App()->params['defaultPageSize']);
        $massiveAction = App()->getController()->renderPartial('/failedEmail/partials/massive_action_selector', [
            'surveyId'    => $surveyId,
            'permissions' => $permissions
        ], true);


        $this->render('failedEmail_index', [
            'failedEmailModel' => $failedEmailModel,
            'pageSize'         => $pageSize,
            'massiveAction'    => $massiveAction,
        ]);
    }

    /**
     * @throws CHttpException
     */
    public function actionResend()
    {
        $surveyId = sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }
        if (!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            App()->user->setFlash('error', gT("You do not have permission to access this page."));
            $this->redirect(['failedEmail/index/', 'surveyid' => $surveyId]);
        }
        $preserveResend = App()->request->getParam('preserveResend') ?? false;
        $item = [App()->request->getParam('item')];
        $items = json_decode(App()->request->getParam('sItems'));
        $selectedItems = $items ?? $item;
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
                    $result = sendSubmitNotifications($surveyId, $emailsByType, $preserveResend, true);
                    if (!$preserveResend) {
                        $criteria->addCondition('status', FailedEmail::STATE_SUCCESS);
                        $failedEmails->deleteAll($criteria);
                    }
                    if ($items) {
                        return $this->renderPartial('partials/modal/resend_result_body', [
                            'successfullEmailCount' => $result['successfullEmailCount'],
                            'failedEmailCount'      => $result['failedEmailCount']
                        ]);
                    }
                    return $this->renderPartial('/admin/super/_renderJson', [
                        "data" => [
                            'success' => true,
                            'html'    => $this->renderPartial('partials/modal/resend_result', [
                                'successfullEmailCount' => $result['successfullEmailCount'],
                                'failedEmailCount'      => $result['failedEmailCount']
                            ], true)
                        ]
                    ]);
                }
            }
            return $this->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => false,
                    'message' => gT('No match could be found for selection'),
                ]
            ]);
        }
        return $this->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => false,
                'message' => gT('Please select at least one item'),
            ]
        ]);
    }

    /**
     * @throws CHttpException|CException
     */
    public function actionDelete(): string
    {
        $surveyId = sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }
        if (!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'delete')) {
            App()->user->setFlash('error', gT("You do not have permission to access this page."));
            $this->redirect(['failedEmail/index/', 'surveyid' => $surveyId]);
        }
        $item = [App()->request->getParam('item')];
        $items = json_decode(App()->request->getParam('sItems'));
        $selectedItems = $items ?? $item;
        if (!empty($selectedItems)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, email_type, recipient';
            $criteria->addCondition('surveyid', $surveyId);
            $criteria->addInCondition('id', $selectedItems);
            $failedEmails = new FailedEmail();
            $deletedCount = $failedEmails->deleteAll($criteria);
            if ($items) {
                return $this->renderPartial('partials/modal/delete_result_body', [
                    'deletedCount' => $deletedCount,
                ]);
            }
            return $this->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => true,
                    'html'    => $this->renderPartial('partials/modal/delete_result', [
                        'deletedCount' => $deletedCount,
                    ], true),
                ]
            ]);
        }
        return $this->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => false,
                'message' => gT('Please select at least one item'),
            ]
        ]);
    }

    public function actionModalContent(): string
    {
        $contentFile = App()->request->getParam('contentFile');
        $id = App()->request->getParam('id');
        $failedEmailModel = new FailedEmail();
        $failedEmail = $failedEmailModel->findByPk($id);
        $surveyId = $failedEmail->surveyid;
        return App()->getController()->renderPartial(
            '/failedEmail/partials/modal/' . $contentFile,
            ['id' => $id, 'surveyId' => $surveyId, 'failedEmail' => $failedEmail]
        );
    }
}
