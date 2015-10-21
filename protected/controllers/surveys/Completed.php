<?php
namespace ls\controllers\surveys;

use ls\models\Survey;

class Completed extends \Action
{
    /**
     * Renders the completed page.
     */
    public function run()
    {
        $session = App()->surveySessionManager->current;
        if ($session->isFinished) {
            echo "Good job you are done with the survey!";
        }
    }

}