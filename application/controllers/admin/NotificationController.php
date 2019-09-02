<?php

/**
 * Mostly for Ajax actions
 */
class NotificationController extends Survey_Common_Action
{

    /**
     * List all notifications for a user
     */
    public function index()
    {
        $this->checkPermission();

        $data = array();
        $data['model'] = Notification::model();

        $this->_renderWrappedTemplate(null, array('notification/index'), $data);
    }

    /**
     * Get notification as JSON
     *
     * @param int $notId Notification id
     * @return string JSON
     */
    public function getNotificationAsJSON($notId)
    {
        $this->checkPermission();
        if ((string) (int) $notId !== (string) $notId) {
            
        }
        $not = Notification::model()->findByPk($notId);
        if (!$not) {
            throw new CHttpException(404, sprintf(gT("Notification %s not found"), $notId));
        }
        header('Content-type: application/json');
        echo json_encode(array('result' => $not->getAttributes()));
    }

    /**
     * Mark notification as read
     *
     * @param int $notId Notification id
     * @return string JSON
     */
    public function notificationRead($notId)
    {
        $this->checkPermission();

        if ((string) (int) $notId !== (string) $notId) {
            throw new CHttpException(403, gT("Invalid notification id"));
        }
        $not = Notification::model()->findByPk($notId);
        if (!$not) {
            throw new CHttpException(404, sprintf(gT("Notification %s not found"), $notId));
        }
        // Check if user is allowed to mark this notification as read
        if ($not->entity=='user' && $not->entity_id<>Yii::app()->user->id) {
            throw new CHttpException(404, sprintf(gT("Invalid notification id"), $notId));
        }
        
        $result = $not->markAsRead();
        header('Content-type: application/json');
        echo json_encode(array('result' => $result));
    }

    /**
     * Spits out html used in admin menu
     * @param int|null $surveyId
     * @param bool $showLoader Whether or not to show spinning loader instead of notification list
     * @return string
     */
    public function actionGetMenuWidget($surveyId = null, $showLoader = false)
    {
        $this->checkPermission();
        echo self::getMenuWidget($surveyId, $showLoader);
    }

    /**
     * Delete all notifications for this user and this survey
     * @param int|null $surveyId
     * @return void
     */
    public function clearAllNotifications($surveyId = null)
    {
        Notification::model()->deleteAll(
            'entity = :entity AND entity_id = :entity_id',
            array(":entity"=>'user', ":entity_id"=>Yii::app()->user->id)
        );

        if (is_int($surveyId)) {
            Notification::model()->deleteAll(
                'entity = :entity AND entity_id = :entity_id',
                array(":entity"=>'survey', ":entity_id"=>$surveyId)
            );
        }
    }

    /**
     * Die if user is not logged in
     * @return void
     */
    protected function checkPermission()
    {
        // Abort if user is not logged in
        if (Yii::app()->user->isGuest) {
            throw new CHttpException(401);
        }
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
        $data['surveyId'] = $surveyId;
        $data['showLoader'] = $showLoader;
        $params = array(
            'sa' => 'clearAllNotifications',
        );
        if ($surveyId !== null) {
            $params['surveyId'] = $surveyId;
        }
        $data['clearAllNotificationsUrl'] = Yii::app()->createUrl('admin/notification', $params);
        $data['updateUrl'] = Notification::getUpdateUrl($surveyId);
        $data['nrOfNewNotifications'] = Notification::countNewNotifications($surveyId);
        $data['nrOfNotifications'] = Notification::countNotifications($surveyId);
        $data['nrOfImportantNotifications'] = Notification::countImportantNotifications($surveyId);
        $data['bellColor'] = $data['nrOfNewNotifications'] == 0 ? 'text-success' : 'text-warning';

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
