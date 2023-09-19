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
        $not = new Notification([
            'user_id' => 1,
            'title' => 'New survey template imported',
            'importance' => Notification::NAG_ONCE_IMPORTANCE,
            'message' => <<<HTML
                Redirecting...
                <script>
                    // Wait 1 sec so that notification has time to be marked as read via Ajax.
                    setTimeout(
                        () => window.location.href = "https://localhost/index.php?r=surveyAdministration/view/surveyid/455171&popuppreview=true",
                        1000
                    );
                </script>
HTML
        ]);
        $not->save();
    }
}
