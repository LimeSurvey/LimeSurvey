<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Saving the answer options of one scale of a dual-scale question as a label set in the classic question editor.
 *
 * @group dualscale
 */
class SaveLabelSetFromDualScaleQuestionTest extends TestBaseClassWeb
{
    /** Name of the label set the test saves */
    private const LABEL_SET_NAME = 'SaveLabelSetFromDualScaleQuestionTest second scale';

    /**
     * Log in to the admin interface.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        $username = getenv('ADMINUSERNAME') ?: 'admin';
        $password = getenv('PASSWORD') ?: 'password';
        self::adminLogin($username, $password);
    }

    /**
     * Delete the label set the test saved.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach (\LabelSet::model()->findAllByAttributes(['label_name' => self::LABEL_SET_NAME]) as $labelSet) {
            // Called on the set itself, as it deletes the labels of $this
            $labelSet->deleteLabelSet($labelSet->lid);
        }
        parent::tearDown();
    }

    /**
     * Each scale has its own Save label set button, and only the rows of that scale are saved,
     * each with its own code and text.
     *
     * @return void
     */
    public function testSaveSecondScaleAsLabelSet(): void
    {
        // One answer option per scale, both with the code A1
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_677328.lss');
        $question = \Survey::model()->findByPk(self::$surveyId)->groups[0]->questions[0];
        $answers = [];
        foreach (\Answer::model()->findAllByAttributes(['qid' => $question->qid]) as $answer) {
            $answers[(int)$answer->scale_id] = $answer;
        }
        $this->assertCount(2, $answers, 'The question needs one answer option per scale');

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'questionAdministration/view',
            ['surveyid' => self::$surveyId, 'gid' => $question->gid, 'qid' => $question->qid]
        );

        $web = self::$webDriver;
        $web->get($url);
        sleep(2);
        $web->dismissModal();
        $web->dismissModal();
        sleep(5);

        $editorButton = $this->waitForElementShim($web, '#questionEditorButton');
        $web->wait(20)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#questionEditorButton'))
        );
        $editorButton->click();
        sleep(1);
        $advancedOptions = $this->waitForElementShim($web, '#advanced-options-container');
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($advancedOptions));
        $web->findElement(WebDriverBy::linkText('Answer options'))->click();

        // Give the second scale its own code and text, so a mix-up with the first scale shows
        $codeInput = $web->findElement(WebDriverBy::cssSelector(
            sprintf('input[name="answeroptions[%d][1][code]"]', $answers[1]->aid)
        ));
        $codeInput->clear();
        $codeInput->sendKeys('B1');
        $textInput = $web->findElement(WebDriverBy::cssSelector(
            sprintf('input[name="answeroptions[%d][1][answeroptionl10n][en]"]', $answers[1]->aid)
        ));
        $textInput->clear();
        $textInput->sendKeys('Second scale');

        $saveButton = $web->findElement(WebDriverBy::id('bthsaveaslabel_1'));
        $web->executeScript('arguments[0].scrollIntoView({block: "center"});', [$saveButton]);
        $saveButton->click();
        $web->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('saveaslabelModal'))
        );
        $web->findElement(WebDriverBy::cssSelector('label[for="newlabel"]'))->click();
        $this->waitForElementShim($web, '#laname')->sendKeys(self::LABEL_SET_NAME);
        $web->findElement(WebDriverBy::id('btnsavelabelset'))->click();

        $labelSet = null;
        for ($second = 0; $second < 20 && $labelSet === null; $second++) {
            sleep(1);
            $labelSet = \LabelSet::model()->findByAttributes(['label_name' => self::LABEL_SET_NAME]);
        }
        $this->assertNotNull($labelSet, 'The label set was not saved');
        $labels = \Label::model()->findAllByAttributes(['lid' => $labelSet->lid]);
        $this->assertCount(1, $labels, 'Only the row of the second scale must be saved');
        $this->assertSame('B1', $labels[0]->code);
        $this->assertSame('Second scale', $labels[0]->labell10ns['en']->title);
    }
}
