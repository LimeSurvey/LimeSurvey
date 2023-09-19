<?php

Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.admin.import_helper', true);

class AddNotificationCommand extends CConsoleCommand
{
    /**
     * Sample command: php application/commands/console.php importsurvey tmp/upload/youfile.lss
     *
     * @param string[] $arguments
     * @return void
     */
    public function run($arguments)
    {
        $title = $arguments[0];
        $body  = $arguments[1];
        $importance = (int) $arguments[2] ?? Notification::NORMAL_IMPORTANCE;

        $not = new Notification([
            'user_id' => 1,
            'title' => $title,
            'importance' => $importance,
            'message' => $body
        ]);
        $not->save();
    }
}
