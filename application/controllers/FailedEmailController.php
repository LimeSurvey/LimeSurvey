<?php

/**
 * @psalm-suppress InvalidScalarArgument
 */
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
        $this->aData['title_bar']['title'] = gT('Failed email notifications');
        $this->aData['subaction'] = gT("Failed email notifications");

        return parent::beforeRender($view);
    }

    /**
     * @throws CHttpException|CException
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

        // Set number of page, else pagination won't work
        $pageSize = App()->request->getParam('pageSize', null);
        if ($pageSize != null) {
            App()->user->setState('pageSize', (int) $pageSize);
        }

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'failedEmail.js', LSYii_ClientScript::POS_BEGIN);
        $failedEmailModel = FailedEmail::model();
        $failedEmailModel->setAttributes(App()->getRequest()->getParam('FailedEmail'), false);
        $failedEmailModel->setAttribute('surveyid', $surveyId);
        $pageSize = App()->request->getParam('pageSize') ?? App()->user->getState('pageSize', App()->params['defaultPageSize']);
        $massiveAction = App()->getController()->renderPartial('/failedEmail/partials/massive_action_selector', [
            'surveyId' => $surveyId,
            'permissions' => $permissions
        ], true);

        $aData = [];
        $topbarData = TopbarConfiguration::getSurveyTopbarData($surveyId);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );

        $this->aData = $aData;

        $this->render('failedEmail_index', [
            'failedEmailModel' => $failedEmailModel,
            'pageSize'         => $pageSize,
            'massiveAction'    => $massiveAction,
        ]);
    }

    /**
     * @throws CHttpException|CException
     * @return string|void
     */
    public function actionResend()
    {
        $surveyId = (int)sanitize_int(App()->request->getParam('surveyid'));
        if (!$surveyId) {
            throw new CHttpException(403, gT("Invalid survey ID"));
        }

        global $thissurvey;
        $thissurvey = getSurveyInfo($surveyId);

        if (!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            App()->user->setFlash('error', gT("You do not have permission to access this page."));
            $this->redirect(['failedEmail/index/', 'surveyid' => $surveyId]);
        }
        $deleteAfterResend = App()->request->getParam('deleteAfterResend');
        $preserveResend = is_null($deleteAfterResend);
        $item = [App()->request->getParam('item')];
        $items = json_decode(App()->request->getParam('sItems', ''));
        $selectedItems = $items ?? $item;
        $emailsByType = [];
        if (!empty($selectedItems)) {
            $criteria = new CDbCriteria();
            /** @psalm-suppress RedundantCast */
            $criteria->addCondition('surveyid = ' . (int) $surveyId);
            $criteria->addInCondition('id', $selectedItems);
            $failedEmails = FailedEmail::model()->findAll($criteria);
            if (!empty($failedEmails)) {
                foreach ($failedEmails as $failedEmail) {
                    $emailsByType[$failedEmail->email_type][] = [
                        'id'        => $failedEmail->id,
                        'responseId' => $failedEmail->responseid,
                        'recipient' => $failedEmail->recipient,
                        'language' => $failedEmail->language,
                        'resendVars' => $failedEmail->resend_vars,
                    ];
                }
                $result = sendSubmitNotifications($surveyId, $emailsByType, true);
                if (!$preserveResend) {
                    // only delete FailedEmail entries that have succeeded
                    $criteria->addCondition('status = :status');
                    $criteria->params['status'] = FailedEmail::STATE_SUCCESS;
                    FailedEmail::model()->deleteAll($criteria);
                }
                // massive action
                if ($items) {
                    return $this->renderPartial('partials/modal/resend_result_body', [
                        'successfullEmailCount' => $result['successfullEmailCount'],
                        'failedEmailCount'      => $result['failedEmailCount']
                    ]);
                }
                // single action
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
     * @return string|void
     */
    public function actionDelete()
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
        $items = json_decode(App()->request->getParam('sItems', ''));
        $selectedItems = $items ?? $item;
        if (!empty($selectedItems)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, email_type, recipient';
            $criteria->addCondition('surveyid =' . (int) $surveyId);
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

    /**
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionModalContent(): void
    {
        $id = App()->request->getParam('id');
        $failedEmailModel = new FailedEmail();
        $failedEmail = $failedEmailModel->findByPk($id);
        if (!$failedEmail) {
            throw new CHttpException(403, gT("Invalid ID"));
        }
        $surveyId = $failedEmail->surveyid;
        $permissions = [
            'update' => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update'),
            'delete' => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'delete'),
            'read'   => Permission::model()->hasSurveyPermission($surveyId, 'responses', 'read')
        ];
        $contentFile = App()->request->getParam('contentFile');
        if (
            !($permissions['update'] && $contentFile === 'resend_form')
            && !($permissions['delete'] && $contentFile === 'delete_form')
            && !($permissions['read'] && in_array($contentFile, ['email_content', 'email_error']))
        ) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        App()->getController()->renderPartial(
            '/failedEmail/partials/modal/' . $contentFile,
            ['id' => $id, 'surveyId' => $surveyId, 'failedEmail' => $failedEmail]
        );
    }
}
