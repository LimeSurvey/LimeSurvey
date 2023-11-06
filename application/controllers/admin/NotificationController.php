<?php

/**
 * Mostly for Ajax actions
 */
class NotificationController extends SurveyCommonAction
{
    /**
     * Get notification as JSON
     *
     * @param int $notId Notification id
     * @return string JSON
     * @throws CHttpException
     */
    public function getNotificationAsJSON($notId)
    {
        $oNotification = $this->checkPermission($notId);
        header('Content-type: application/json');
        echo json_encode(array('result' => $oNotification->getAttributes()));
    }

    /**
     * Mark notification as read
     *
     * @param int $notId Notification id
     * @return void JSON
     * @throws CHttpException
     */
    public function notificationRead($notId)
    {
        $oNotification = $this->checkPermission($notId);

        // Check if user is allowed to mark this notification as read
        if ($oNotification->entity == 'user' && $oNotification->entity_id <> Yii::app()->user->id) {
            throw new CHttpException(404, sprintf(gT("Invalid notification id"), $notId));
        }

        $result = $oNotification->markAsRead();
        header('Content-type: application/json');
        echo json_encode(array('result' => $result));
    }

    /**
     * Spits out html used in admin menu
     * @param int|null $surveyId
     * @param bool $showLoader show spinning loader instead of notification list
     * @return void
     * @throws CHttpException|CException
     */
    public function actionGetMenuWidget($surveyId = null, $showLoader = false)
    {
        if (App()->user->isGuest) {
            throw new CHttpException(401);
        }
        echo self::getMenuWidget($surveyId, $showLoader);
    }

    /**
     * Delete all notifications for this user and this survey
     * @param int|null $surveyId
     * @return void
     * @throws CHttpException
     */
    public function clearAllNotifications($surveyId = null)
    {
        if (App()->request->isPostRequest) {
            if (App()->user->isGuest) {
                throw new CHttpException(401);
            }
            Notification::model()->deleteAll(
                'entity = :entity AND entity_id = :entity_id',
                [":entity" => 'user', ":entity_id" => App()->user->id]
            );
            if (is_null($surveyId)) {
                $surveyId = App()->request->getPost('surveyId');
            }
            if (!is_null($surveyId)) {
                $surveyId = (int)$surveyId;
                if (Permission::model()->hasSurveyPermission($surveyId, 'survey', 'update')) {
                    Notification::model()->deleteAll(
                        'entity = :entity AND entity_id = :entity_id',
                        [":entity" => 'survey', ":entity_id" => $surveyId]
                    );
                }
            }
        }
    }

    /**
     * Check if the user has permission to access this notification
     *
     * Returns Notification object if the user has permission, throws CHttpException otherwise
     * @param $notId
     * @return Notification
     * @throws CHttpException
     */
    protected function checkPermission($notId): Notification
    {
        // Abort if user is not logged in
        $oNotification = Notification::model()->findByPk($notId);
        if (!$oNotification) {
            throw new CHttpException(404, sprintf(gT("Notification %s not found"), $notId));
        }
        if ((int) $oNotification->entity_id !== (int) App()->user->id) {
            throw new CHttpException(403, gT("You do not have permission to access this page/function."));
        }

        return $oNotification;
    }

    /**
     * Get menu HTML for notifications
     *
     * @param int|null $surveyId
     * @param bool $showLoader If true, show spinning loader instead of messages (fetch them using ajax)
     * @return string HTML
     * @throws CException
     */
    public static function getMenuWidget($surveyId = null, $showLoader = false)
    {
        // Make sure database version is high enough.
        // This is needed since admin bar is loaded during
        // database update procedure.
        if (Yii::app()->getConfig('DBVersion') < 259) {
            return '';
        }

        $data = array();
        $data['surveyId'] = (int) $surveyId;
        $data['showLoader'] = $showLoader;
        if ($surveyId !== null) {
            $surveyIdParam = 'surveyId=' . $surveyId;
        } else {
            $surveyIdParam = '';
        }
        $data['clearAllNotificationsUrl'] = App()->createUrl('admin/notification', ['sa' => 'clearAllNotifications']);
        $data['clearAllNotificationsParams'] = $surveyIdParam;
        $data['updateUrl'] = Notification::getUpdateUrl($surveyId);
        $data['nrOfNewNotifications'] = Notification::countNewNotifications($surveyId);
        $data['nrOfNotifications'] = Notification::countNotifications($surveyId);
        $data['nrOfImportantNotifications'] = Notification::countImportantNotifications($surveyId);
        $data['bellColor'] = $data['nrOfNewNotifications'] == 0 ? '' : '';

        // If we have any important notification we might as well load everything
        if ($data['nrOfImportantNotifications'] > 0) {
            $data['showLoader'] = false;
        }

        // Only load all messages when we're not showing spinning loader
        if (!$data['showLoader']) {
            $data['notifications'] = Notification::getNotifications($surveyId);
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/admin_notifications',
            $data,
            true
        );
    }
}
