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

/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 26.01.18
 * Time: 18:55
 */

namespace ls\tests\controllers;


use ls\tests\TestBaseClass;
use Yii;

class SurveyActivatorTest extends TestBaseClass
{
    public static $surveyWithTimingsID = '88881';
    public static $surveyWithoutTimingsID = '143933';
    public static $surveyWithFileUploadID = '561859';

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
    }

    public function testActivateWithTimings(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $this->assertTrue($result['status']==='OK');
    }

    public function testParticipantsTableCreated(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $table = Yii::app()->db->schema->getTable(self::$testSurvey->responsesTableName);
        $this->assertNotEmpty($table);
    }

    public function testTimingsTableCreated(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $table = Yii::app()->db->schema->getTable(self::$testSurvey->timingsTableName);
        $this->assertNotEmpty($table);
    }

    public function testTimingsTableNotCreated(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithoutTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $table = Yii::app()->db->schema->getTable(self::$testSurvey->timingsTableName);
        $this->assertEmpty($table);
    }

    public function testActivateWithoutTimings(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithoutTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $this->assertTrue($result['status']==='OK');
    }

    public function testSurveyFolderCreated(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithFileUploadID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        $folder = Yii::app()->getConfig('uploaddir')."/surveys/".self::$surveyId."/files";
        $this->assertTrue(file_exists($folder));
    }
}