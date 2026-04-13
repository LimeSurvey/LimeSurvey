<?php
/**
 *  LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

namespace ls\tests\controllers;

use ls\tests\TestBaseClassView;

/**
 * Class AdminViewsTest
 * This test loops through all basic admin view pages and cheks if they open withour errors
 *
 * @package ls\tests
 * @group adminviews
 */
class AdminViewsTest extends TestBaseClassView
{

    public function addBaseViews()
    {
        return require __DIR__."/../../data/views/adminBaseViews.php";
    }
    
    public function addSurveyViews()
    {
        return require __DIR__."/../../data/views/adminSurveyViews.php";
    }

    public function addSettingsViews()
    {
        return require __DIR__."/../../data/views/adminSettingsViews.php";
    }

    public function addUsersViews()
    {
        return require __DIR__."/../../data/views/adminUsersViews.php";
    }
    public function addParticipantsViews()
    {
        return require __DIR__."/../../data/views/adminParticipantsViews.php";
    }

    public function addGeneralSettingsViews()
    {
        return require __DIR__."/../../data/views/adminGeneralSettingsViews.php";
    }

    /**
     * @param string $name
     * @param array$view
     * @dataProvider addBaseViews
     * 
     */
    public function testAdminViews($name, $view)
    {
        if ($name=='login') {
            // skip login
            $this->assertTrue(true);
            return;
        }
        $this->findViewTag($name, $view);
    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addSurveyViews
     */
    public function testAdminSurveyViews($name, $view)
    {
        if (isset($view['import_id'])) {
            // we'll change the survey in the middle of test
            if (self::$testSurvey) {
                self::$testSurvey->delete();
            }
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_'.$view['import_id'].'.lss';
            self::importSurvey($surveyFile);

            if (isset($view['activate']) && $view['activate']) {
                $activator = new \SurveyActivator(self::$testSurvey);
                $activator->activate();
                if (!tableExists("{{tokens_" . self::$surveyId . "}}")) {
                    \Token::createTable(self::$surveyId);
                }
            }
        } elseif (empty(self::$surveyId)) {
            // This situation can happen if we test only one data entry,
            // using --filter="testAdminSurveyViews#13" (for data entry 13).
            $surveyFile = self::$surveysFolder . '/../data/surveys/limesurvey_survey_454287.lss';
            self::importSurvey($surveyFile);
        }
        if (isset($view['questionType'])) {
            $question = self::$testSurvey->findQuestionByType($view['questionType']);
            if (empty($question)) {
                throw new \Exception('Question not found');
            }
            $view['route'] = ReplaceFields(
                $view['route'],
                ['{QID}'=> $question->qid,'{GID}'=> $question->gid,'{SID}'=> self::$testSurvey->primaryKey]
            );

        }
        $view['route'] = ReplaceFields($view['route'], ['{SID}'=> self::$testSurvey->primaryKey]);
        $this->findViewTag($name, $view);
    }

    /**
     * @param string $name
     * @param array$view
     * @dataProvider addSettingsViews
     */
    public function testSettingsViews($name, $view)
    {
        $this->findViewTag($name, $view);
    }

    /**
     * @param string $name
     * @param array$view
     * @dataProvider addGeneralSettingsViews
     */
    public function testGeneralSettingsViews($name, $view)
    {
        $this->findViewTag($name, $view);
    }

    /**
     * @param string $name
     * @param array$view
     * @dataProvider addParticipantsViews
     */
    public function testParticipantsViews($name,$view){
        $this->findViewTag($name, $view);
    }
}
