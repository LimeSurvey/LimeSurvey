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

namespace LimeSurvey\tests\acceptance\admin;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use LimeSurvey\tests\TestBaseClassView;

/**
 * Class AdminViewsTest
 * This test loops through all basic admin view pages and cheks if they open withour errors
 *
 * @package LimeSurvey\tests
 * @group adminviews
 */
class AdminViewsTest extends TestBaseClassView
{

    public function addBaseViews()
    {
        return require self::getViewsFolder()."/adminBaseViews.php";
    }

    public function addSurveyViews()
    {
        return require self::getViewsFolder()."/adminSurveyViews.php";
    }

    public function addSettingsViews()
    {
        return require self::getViewsFolder()."/adminSettingsViews.php";
    }

    public function addUsersViews()
    {
        return require self::getViewsFolder()."/adminUsersViews.php";
    }
    public function addParticipantsViews()
    {
        return require self::getViewsFolder()."/adminParticipantsViews.php";
    }

    public function addGeneralSettingsViews()
    {
        return require self::getViewsFolder()."/adminGeneralSettingsViews.php";
    }

    public function addAdminClickViews()
    {
        return require __DIR__."/../data/views/adminClickViews.php";
    }

    /**
     * @param string $name
     * @param array $view
     * @throws \Exception
     * @dataProvider addBaseViews
     */
    public function testAdminViews($name, $view)
    {
        if ($name=='login') {
            // skip login
            $this->assertTrue(true);
            return;
        }
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);

    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addSurveyViews
     * @throws \Exception
     */
    public function testAdminSurveyViews($name, $view)
    {
        if (isset($view['import_id'])) {

            // we'll change the survey in the middle of test
            if(self::$testSurvey){
                self::$testSurvey->delete();
            }
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_'.$view['import_id'].'.lss';
            self::importSurvey($surveyFile);


        } elseif (empty(self::$surveyId)) {
            // This situation can happen if we test only one data entry,
            // using --filter="testAdminSurveyViews#13" (for data entry 13).
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_454287.lss';
            self::importSurvey($surveyFile);

        }
        $view['route'] = ReplaceFields($view['route'], ['{SID}'=> self::$testSurvey->primaryKey]);
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);
    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addSettingsViews
     * @throws \Exception
     */
    public function testSettingsViews($name, $view)
    {
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);
    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addUsersViews
     * @throws \Exception
     */
    public function testUserViews($name,$view){
        // use Admin user
        $user = \User::findByUsername(self::$superUserUsername);
        $view['route'] = ReplaceFields($view['route'],['{UID}'=>$user->uid]);
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);
    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addGeneralSettingsViews
     * @throws \Exception
     */
    public function testGeneralSettingsViews($name, $view)
    {
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);
    }

    /**
     * @param string $name
     * @param array $view
     * @dataProvider addParticipantsViews
     * @throws \Exception
     */
    public function testParticipantsViews($name,$view){
        $element = $this->openAndFindViewTag($name, $view);
        $this->assertNotEmpty($element);
    }



    /**
     * @param string $name
     * @param array $view
     * @throws \Exception
     * @dataProvider addAdminClickViews
     */
    public function testAdminClickViews($name,$view){

        $user = self::$user;
        if(isset($view['username'])){
            $user = \User::findByUsername($view['username']);
        }

        $view['clickId'] = ReplaceFields($view['clickId'],['{UID}'=>$user->primaryKey]);

        $url = $this->getUrl($view);
        $this->openView($url);

        //close modal (eg password warning)
        self::findAndClick(WebDriverBy::cssSelector('#admin-notification-modal button.close'),2);

        self::findAndClick(WebDriverBy::id($view['clickId']),10);
        $element = $this->findViewTag($name,10);
        $this->assertNotEmpty(
            $element,
            sprintf(
                'FAILED viewing %s and clicking on %s, url %s',
                $name,
                (isset($view['clickId']) ? $view['clickId'] : ''),
                $url
            )
        );

    }

}
