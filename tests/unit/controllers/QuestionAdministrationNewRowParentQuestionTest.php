<?php

namespace ls\tests\unit\controllers;

use ls\tests\TestBaseClass;

require_once __DIR__ . '/../../../application/controllers/QuestionAdministrationController.php';

/**
 * Subquestion and answer option rows added via AJAX must pass the parent question to the editor (placeholder picker)
 */
class QuestionAdministrationNewRowParentQuestionTest extends TestBaseClass
{
    /** @var \Question */
    private static $parentQuestion;

    /** @var \QuestionAdministrationController */
    private $controller;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_expressionAnswerOptionsTest.lss');
        self::$parentQuestion = \Question::model()->find(
            'sid = :sid AND type = :type AND parent_qid = 0',
            [':sid' => self::$surveyId, ':type' => 'F']
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new \QuestionAdministrationController('questionAdministration');
        \Yii::app()->setController($this->controller);
    }

    protected function tearDown(): void
    {
        unset($_GET['qid'], $_GET['surveyid']);
        parent::tearDown();
    }

    /**
     * @param string $methodName
     * @param mixed ...$args
     * @return mixed
     */
    private function invokePrivate($methodName, ...$args)
    {
        $method = new \ReflectionMethod($this->controller, $methodName);
        $method->setAccessible(true);
        return $method->invoke($this->controller, ...$args);
    }

    /**
     * @param string $html
     * @return string the editor url of the rendered row
     */
    private function getEditorUrl($html)
    {
        $this->assertSame(1, preg_match('/[^"\']*htmleditorpop[^"\']*/', $html, $matches), 'Editor url not found');
        return html_entity_decode($matches[0]);
    }

    /**
     * @param string $url
     * @param string $name
     * @return string|null
     */
    private function getUrlParam($url, $name)
    {
        if (preg_match('#[/&?]' . $name . '[/=]([^/&]*)#', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function testParentQuestionFromRequest()
    {
        $this->assertNotNull(self::$parentQuestion);
        $subquestion = \Question::model()->find('parent_qid = :qid', [':qid' => self::$parentQuestion->qid]);
        $this->assertNotNull($subquestion);

        $this->assertNull($this->invokePrivate('getParentQuestionFromRequest', self::$surveyId));

        $_GET['qid'] = self::$parentQuestion->qid;
        $this->assertSame(
            (int) self::$parentQuestion->qid,
            (int) $this->invokePrivate('getParentQuestionFromRequest', self::$surveyId)->qid
        );
        $this->assertNull($this->invokePrivate('getParentQuestionFromRequest', self::$surveyId + 1));

        $_GET['qid'] = $subquestion->qid;
        $this->assertNull($this->invokePrivate('getParentQuestionFromRequest', self::$surveyId));
    }

    public function testNewSubquestionRowEditorUsesParentQuestion()
    {
        $_GET['surveyid'] = self::$surveyId;
        $_GET['qid'] = self::$parentQuestion->qid;
        $html = $this->invokePrivate(
            'getSubquestionRow',
            self::$surveyId,
            self::$parentQuestion->gid,
            'new12345',
            json_encode(['SQ001']),
            'en',
            'false',
            0,
            1
        );
        $url = $this->getEditorUrl($html);
        $this->assertSame((string) self::$surveyId, $this->getUrlParam($url, 'sid'), $url);
        $this->assertSame((string) self::$parentQuestion->gid, $this->getUrlParam($url, 'gid'), $url);
        $this->assertSame((string) self::$parentQuestion->qid, $this->getUrlParam($url, 'qid'), $url);
    }

    public function testNewAnswerOptionRowEditorUsesParentQuestion()
    {
        $_GET['surveyid'] = self::$surveyId;
        $_GET['qid'] = self::$parentQuestion->qid;
        $html = $this->invokePrivate(
            'getAnswerOptionRow',
            self::$surveyId,
            self::$parentQuestion->gid,
            'new12345',
            json_encode(['A1']),
            'en',
            'false',
            0,
            1
        );
        $url = $this->getEditorUrl($html);
        $this->assertSame((string) self::$surveyId, $this->getUrlParam($url, 'sid'), $url);
        $this->assertSame((string) self::$parentQuestion->gid, $this->getUrlParam($url, 'gid'), $url);
        $this->assertSame((string) self::$parentQuestion->qid, $this->getUrlParam($url, 'qid'), $url);
    }
}
