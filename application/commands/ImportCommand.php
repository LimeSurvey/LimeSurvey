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
     * ImportCommand constructor.
     * @param $name
     * @param CConsoleCommandRunner $runner
     * @throws CException
     */
    public function __construct($name, CConsoleCommandRunner $runner)
    {
        parent::__construct($name, $runner);
        \Yii::import('application.helpers.admin.import_helper', true);
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
    }


    /**
     * @throws Exception
     */
    public function actionIndex($file,$userId=null)
    {

        if(!$file){
            echo 'File name must be defined. Use --file= argument to define file path.'.PHP_EOL;;
            exit(1);

        }

        if($file[0]===DIRECTORY_SEPARATOR){
            // we have root path set
            $surveyFile = $file;
        }else{
            $surveyFile = __DIR__ . '/../../'.$file;
        }


        if (!file_exists($surveyFile)) {
            echo sprintf('Fatal error: found no survey file at "%s"',$surveyFile).PHP_EOL;;
            exit(1);
        }

        if($userId){
            $user = User::model()->findByPk($userId);
        }else{
            echo 'No user is set'.PHP_EOL;
            $superAdmins = User::getSuperAdmins();
            if(!empty($superAdmins)){
                $user = $superAdmins[0];
                echo sprintf('Using user %s (userId=%d) by default',$user->users_name, $user->primaryKey).PHP_EOL;
            }
        }
        if(!$user){
            echo 'Fatal error: User not found'.PHP_EOL;
            echo 'Specify the user id by --userId=[uid] or leave blank to use a default superadmin.'.PHP_EOL;
            exit(1);
        }



        $translateLinksFields = false;
        $newSurveyName = null;
        try {
            $result = importSurveyFile(
                $surveyFile,
                $translateLinksFields,
                $user,
                $newSurveyName,
                null
            );
            if($result){
                $newSid = $result['newsid'];
                $newSurvey = Survey::model()->findByPk($newSid);
                echo sprintf('Successfully imported survey').PHP_EOL;
                echo sprintf('Imported survey ID: %d',$newSurvey->primaryKey).PHP_EOL;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

    }

}