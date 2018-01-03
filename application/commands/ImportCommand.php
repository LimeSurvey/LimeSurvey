<?php
/**
 *  LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class ImportCommand extends CConsoleCommand
{
    /**
     * @throws CException
     */
    public function run($args)
    {
        /** @global ConsoleApplication $app */
        global $app;

        \Yii::import('application.helpers.admin.import_helper', true);
        \Yii::import('application.helpers.common_helper', true);

        $fileName = __DIR__ . '/../../'.$args[0];
        $surveyFile = $fileName;

        if (!file_exists($surveyFile)) {
            echo 'Fatal error: found no survey file';
            exit(1);
        }
        $user = User::model()->findByPk(Yii::app()->session['loginID']);

        $translateLinksFields = false;
        $newSurveyName = null;
        try {
            $result = importSurveyFile(
                $fileName,
                $translateLinksFields,
                $user,
                $newSurveyName,
                null
            );
        } catch (\Exception $ex) {
            throw $ex;
        }

    }

}