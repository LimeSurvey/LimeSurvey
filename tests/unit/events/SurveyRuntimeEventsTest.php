<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginEvent;
use Survey;

/**
 * Tests the plugin events dispatched while a participant takes a survey:
 * afterFindSurvey, beforeSurveyPage, beforeQuestionRender and afterSurveyComplete.
 *
 * The survey pages are requested inside the test process, so the test plugin gets the events.
 */
class SurveyRuntimeEventsTest extends TestBaseClass
{
    /** @var \EventRecorderPlugin */
    private $recorder;

    /** @var string|null Request method before the test, null if there was none */
    private $requestMethod;

    /**
     * Import an all in one survey with a single text question, remove its expiry date and activate it.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_666368.lss');
        // The survey of the file is expired
        Survey::model()->updateByPk(self::$surveyId, ['expires' => null]);
        Survey::model()->resetCache();
        self::$testSurvey = Survey::model()->findByPk(self::$surveyId);
        $result = (new \SurveyActivator(self::$testSurvey))->activate();
        self::assertSame('OK', $result['status'] ?? null, 'The test survey could not be activated.');
        // The cached table names do not include the response table created by the activation yet
        App()->db->schema->refresh();
        // The survey model and the survey info cached by the import are still inactive
        Survey::model()->resetCache();
        \getSurveyInfo(self::$surveyId, '', true);
    }

    /**
     * Start every test without a survey session.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        require_once self::$dataFolder . '/plugins/EventRecorderPlugin.php';
        $this->recorder = new \EventRecorderPlugin(App()->getPluginManager(), 0);
    }

    /**
     * Remove the event subscriptions and reset the request and session data.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->recorder->stopListening();
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        Survey::model()->resetCache();
        parent::tearDown();
    }

    /**
     * A plugin can change the settings of a survey in afterFindSurvey: the survey model
     * has the changed value, the database keeps the saved one.
     *
     * @return void
     */
    public function testPluginCanChangeSurveySettingsInAfterFindSurvey(): void
    {
        $surveyIds = [];
        $this->recorder->listen('afterFindSurvey', function (PluginEvent $event) use (&$surveyIds) {
            $surveyIds[] = $event->get('surveyid');
            if ($event->get('surveyid') == self::$surveyId) {
                $event->set('format', 'G');
            }
        });

        Survey::model()->resetCache();
        $survey = Survey::model()->findByPk(self::$surveyId);

        $this->assertEquals([self::$surveyId], $surveyIds);
        $this->assertSame('G', $survey->format);
        $savedFormat = App()->db->createCommand()
            ->select('format')
            ->from('{{surveys}}')
            ->where('sid = :sid', [':sid' => self::$surveyId])
            ->queryScalar();
        $this->assertSame('A', $savedFormat);
    }

    /**
     * Displaying the survey dispatches beforeSurveyPage once, then beforeQuestionRender
     * once for the question, with the data of the survey and of the question.
     *
     * @return void
     */
    public function testDisplayingTheSurveyDispatchesPageAndQuestionEvents(): void
    {
        list($question, , $fieldName) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $pageSurveyId = null;
        $questionData = [];
        $this->recorder->listen('beforeSurveyPage', function (PluginEvent $event) use (&$pageSurveyId) {
            $pageSurveyId = $event->get('surveyId');
        });
        $this->recorder->listen('beforeQuestionRender', function (PluginEvent $event) use (&$questionData) {
            foreach (['surveyId', 'qid', 'gid', 'code', 'type'] as $key) {
                $questionData[$key] = $event->get($key);
            }
            $questionData['answers'] = $event->get('answers');
        });

        $html = $this->requestSurveyPage(['sid' => self::$surveyId]);

        $this->assertSame(['beforeSurveyPage', 'beforeQuestionRender'], $this->recorder->dispatched);
        $this->assertEquals(self::$surveyId, $pageSurveyId);
        $answers = $questionData['answers'];
        unset($questionData['answers']);
        $this->assertEquals(
            ['surveyId' => self::$surveyId, 'qid' => $question->qid, 'gid' => $question->gid, 'code' => 'Q00', 'type' => 'T'],
            $questionData
        );
        $this->assertStringContainsString('name="' . $fieldName . '"', $answers, 'The event has the answer HTML of the question.');
        $this->assertStringContainsString('name="' . $fieldName . '"', $html, 'The page shows the question.');
    }

    /**
     * A plugin can change the text, the help and the answer HTML of a question in beforeQuestionRender.
     *
     * @return void
     */
    public function testPluginCanChangeTheQuestionInBeforeQuestionRender(): void
    {
        $this->recorder->listen('beforeQuestionRender', function (PluginEvent $event) {
            $event->set('text', 'Question text of the test plugin');
            $event->set('help', 'Question help of the test plugin');
            $event->set('answers', $event->get('answers') . '<p>Answer part of the test plugin</p>');
        });

        $html = $this->requestSurveyPage(['sid' => self::$surveyId]);

        $this->assertStringContainsString('Question text of the test plugin', $html);
        $this->assertStringContainsString('Question help of the test plugin', $html);
        $this->assertStringContainsString('<p>Answer part of the test plugin</p>', $html);
    }

    /**
     * Submitting the survey dispatches afterSurveyComplete once, with the survey ID and the ID
     * of the saved response, and the content added by the plugin is shown on the end page.
     *
     * @return void
     */
    public function testSubmittingTheSurveyDispatchesAfterSurveyComplete(): void
    {
        list(, , $fieldName) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $completed = [];
        $this->recorder->listen('afterSurveyComplete', function (PluginEvent $event) use (&$completed) {
            $completed[] = ['surveyId' => $event->get('surveyId'), 'responseId' => $event->get('responseId')];
            $event->getContent($this->recorder)->addContent('End page content of the test plugin');
        });

        $surveyPage = $this->requestSurveyPage(['sid' => self::$surveyId]);
        $this->assertSame([], $completed, 'Displaying the survey does not complete it.');

        $post = $this->getHiddenFields($surveyPage);
        $post[$fieldName] = 'Answer of the test';
        $post['move'] = 'movesubmit';
        $endPage = $this->requestSurveyPage(['sid' => self::$surveyId], $post);

        // Displaying a survey can create a response too, but only the submitted one is completed
        $responses = App()->db->createCommand()
            ->select(['id', $fieldName])
            ->from('{{responses_' . self::$surveyId . '}}')
            ->where('submitdate IS NOT NULL')
            ->queryAll();
        $this->assertCount(1, $responses, 'Submitting the survey completed one response.');
        $this->assertSame('Answer of the test', $responses[0][$fieldName]);
        $this->assertEquals([['surveyId' => self::$surveyId, 'responseId' => $responses[0]['id']]], $completed);
        $this->assertStringContainsString('End page content of the test plugin', $endPage);
    }

    /**
     * Request a page of the survey like a browser does, inside the test process.
     *
     * @param array<string, mixed> $get The GET parameters of the request
     * @param array<string, mixed> $post The POST parameters of the request; empty for a GET request
     * @return string The HTML of the page
     */
    private function requestSurveyPage(array $get, array $post = []): string
    {
        $_GET = $get;
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = empty($post) ? 'GET' : 'POST';

        // The survey page ends the request, which would end the test process too: leave the request
        // with an exception instead. Yii raises onEndRequest only once, so that state is reset for each request.
        $endOfRequest = new class ('End of the request') extends \RuntimeException {
        };
        $endRequestHandler = function () use ($endOfRequest) {
            throw $endOfRequest;
        };
        $requestEnded = new \ReflectionProperty(\CApplication::class, '_ended');
        $wasRequestEnded = $requestEnded->getValue(App());
        $requestEnded->setValue(App(), false);
        App()->attachEventHandler('onEndRequest', $endRequestHandler);
        $controller = App()->getController();
        $html = '';
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            App()->runController('survey/index');
        } catch (\RuntimeException $exception) {
            if ($exception !== $endOfRequest) {
                throw $exception;
            }
        } finally {
            while (ob_get_level() > $bufferLevel) {
                $html = ob_get_clean() . $html;
            }
            App()->detachEventHandler('onEndRequest', $endRequestHandler);
            $requestEnded->setValue(App(), $wasRequestEnded);
            App()->setController($controller);
        }
        return $html;
    }

    /**
     * Get the hidden form fields of a survey page, to post them back with the answers.
     *
     * @param string $html The HTML of the survey page
     * @return array<string, string> The field values indexed by field name
     */
    private function getHiddenFields($html): array
    {
        $document = new \DOMDocument();
        $useInternalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        $fields = [];
        foreach ($document->getElementsByTagName('input') as $input) {
            if ($input->getAttribute('type') === 'hidden' && $input->getAttribute('name') !== '') {
                $fields[$input->getAttribute('name')] = $input->getAttribute('value');
            }
        }
        return $fields;
    }
}
