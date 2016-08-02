<?php

/**
 * Mostly for Ajax actions
 */
class NotificationController extends LSYii_Controller
{

    /**
     * Get notification as JSON
     *
     * @param int $notId Notification id
     * @return string JSON
     */
    public function actionGetNotificationAsJSON($notId)
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
    public function actionNotificationRead($notId)
    {
        $this->checkPermission();

        try
        {
            $not = Notification::model()->findByPk($notId);
            $not->read = date('Y-m-d H:i:s', time());
            $not->status = 'read';
            $not->save();
            echo json_encode(array('result' => true));
        }
        catch (Exception $ex)
        {
            echo json_encode(array('error' => $ex->getMessage()));
        }

    }

    /**
     * Spits out html used in admin menu
     * @param int|null $surveyId
     * @return string
     */
    public function actionGetMenuWidget($surveyId = null)
    {
        $this->checkPermission();

        echo self::getMenuWidget($surveyId);
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
     * @return string
     */
    public static function getMenuWidget($surveyId = null) {
        $data = array();
        $data['notifications'] = Notification::getNotifications($surveyId);
        $data['zeroNotifications'] = count($data['notifications']) === 0;
        $data['surveyId'] = $surveyId;
        $data['allNotificationsUrl'] = Yii::app()->createUrl('notification', array());

        return Yii::app()->getController()->renderPartial(
            '/admin/super/admin_notifications',
            $data,
            true
        );
    }

}
