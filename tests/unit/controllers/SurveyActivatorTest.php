<?php

namespace ls\tests\controllers;

/**
 *  LimeSurvey
 * Copyright (C) 2007-2011 The GititSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * GititSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


use ls\tests\TestBaseClass;
use Yii;

class SurveyActivatorTest extends TestBaseClass
{
    public static $surveyWithTimingsID = '88881';
    public static $surveyWithoutTimingsID = '143933';
    public static $surveyWithFileUploadID = '561859';

    public static function setupBeforeClass(): void
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

    public function testCreateTokenTable(){
        $file = self::$surveysFolder.DIRECTORY_SEPARATOR.'limesurvey_survey_'.self::$surveyWithTimingsID.'.lss';
        parent::importSurvey($file);
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();
        \Token::createTable(self::$surveyId);
        $table = Yii::app()->db->schema->getTable(self::$testSurvey->tokensTableName);
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
        $folder = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR."surveys".DIRECTORY_SEPARATOR.self::$surveyId.DIRECTORY_SEPARATOR."files";
        $this->assertTrue(file_exists($folder));
    }
}