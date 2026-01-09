<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * Tests issue #14998, a problem with emcache and 'self.NAOK' expressions.
 *
 * @since 2019-07-01
 */
class SelfExpressionTest extends TestBaseClassWeb
{
    /**
     * Test
     */
    public function testBasic()
    {
        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        /** @var string */
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_334427.lss';

        self::importSurvey($surveyFile);

        // Preview survey.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');

        /** @var string */
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'en'
            ]
        );

        // Get questions.
        /** @var Survey */
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        /** @var WebDriver */
        $web = self::$webDriver;

        try {
            // Get first page.
            $web->get($url);

            $web->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            // Click next.
            $web->next();

            $web->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            /** @var RemoteWebElement */
            $modalButton = $web->findElement(WebDriverBy::cssSelector('.modal-dialog button'));
            $modalButton->click();

            sleep(1);

            /** @var string */
            $sgqa = 'Q' . $questions['Q00']->qid;

            /** @var RemoteWebElement */
            $textarea = $web->findElement(WebDriverBy::id('answer' . $sgqa));
            $textarea->sendKeys('asd');

            // Click next.
            $web->next();

            sleep(1);

            /** @var RemoteWebElement */
            $modalButton = $web->findElement(WebDriverBy::cssSelector('.modal-dialog button'));
            $modalButton->click();

            sleep(1);

            /** @var string */
            $sgqa2 = 'Q' . $questions['Q01']->qid;

            /** @var RemoteWebElement */
            $textarea2 = $web->findElement(WebDriverBy::id('answer' . $sgqa2));
            $textarea2->sendKeys('qwe');

            // Click next.
            $web->next();

            sleep(1);

            /** @var RemoteWebElement */
            $completedText = $web->findElement(WebDriverBy::cssSelector('.completed-text p'));
            $this->assertEquals('Thank you!', $completedText->getText());
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }

    }
}
