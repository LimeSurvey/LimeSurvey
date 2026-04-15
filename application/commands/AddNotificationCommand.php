<?php

class AddNotificationCommand extends CConsoleCommand
{
    /**
     * Sample command: php application/commands/console.php addnotification --title='Message title' --body='Hello, this is message body' --importance=1
     *
     * @param string $title Title of notification
     * @param string $body Text body of notification (can contain HTML)
     * @param int $importance Importance level as defined in application/models/Notification.php
     * @return void
     */
    public function actionIndex($title, $body, $importance = 1)
    {
        $not = new Notification([
            'user_id' => 1,
            'title' => $title,
            'importance' => $importance,
            'message' => $body
        ]);
        $not->save();
    }
}
