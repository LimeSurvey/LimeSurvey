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

        $not = Notification::model()->findByPk($notId);

        if ($not)
        {
            echo json_encode(array('result' => $not->getAttributes()));
        }
        else
        {
            echo json_encode(array('error' => 'Found no notification with id ' . $notId));
        }
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

        try
        {
            $not = Notification::model()->findByPk($notId);
            $result = $not->markAsRead();
            echo json_encode(array('result' => $result));
        }
        catch (Exception $ex)
        {
            echo json_encode(array('error' => $ex->getMessage()));
        }

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
            'entity = \'user\' AND entity_id = ' . Yii::app()->user->id
        );

        if (is_int($surveyId))
        {
            Notification::model()->deleteAll(
                'entity = \'survey\' AND entity_id = ' . $surveyId
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
        if(Yii::app()->user->isGuest)
        {
            die('No permission');
        }
    }

    /**
     * Get menu HTML for notifications
     *
     * @param int|null $surveyId
     * @param bool $showLoader If true, show spinning loader instead of messages (fetch them using ajax)
     * @return string HTML
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
        $data['clearAllNotificationsUrl'] = Yii::app()->createUrl('admin/notification', array(
            'sa' => 'clearAllNotifications',
            'surveyId' => $surveyId
        ));
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
