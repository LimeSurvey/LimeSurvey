<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyDeactivate;
use LimeSurvey\PluginManager\PluginEvent;
use Survey;

/**
 * Tests the plugin events dispatched when a survey is activated or deactivated:
 * beforeSurveyActivate, afterSurveyActivate, beforeSurveyDeactivate and afterSurveyDeactivate.
 */
class SurveyActivationEventsTest extends TestBaseClass
{
    /** @var \EventRecorderPlugin */
    private $recorder;

    /**
     * Import a fresh copy of the test survey, so every test starts with an inactive survey.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        require_once self::$dataFolder . '/plugins/EventRecorderPlugin.php';
        $this->recorder = new \EventRecorderPlugin(App()->getPluginManager(), 0);
    }

    /**
     * Remove the event subscriptions and delete the survey imported in setUp().
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->recorder->stopListening();
        App()->db->schema->refresh();
        App()->session['loginID'] = 1;
        Survey::model()->deleteSurvey(self::$surveyId);
        self::$testSurvey = null;
        parent::tearDown();
    }

    /**
     * Activating a survey dispatches beforeSurveyActivate while the survey is still inactive
     * and afterSurveyActivate once the survey is active and has its response table.
     *
     * @return void
     */
    public function testActivationDispatchesBeforeAndAfterEvents(): void
    {
        $states = [];
        $recordState = function (PluginEvent $event) use (&$states) {
            $states[$event->getEventName()] = [
                'surveyId' => $event->get('surveyId'),
                'simulate' => $event->get('simulate'),
                'active' => $this->getActiveState(),
                'hasResponseTable' => $this->hasResponseTable(),
            ];
        };
        $this->recorder->listen('beforeSurveyActivate', $recordState);
        $this->recorder->listen('afterSurveyActivate', $recordState);

        $result = (new \SurveyActivator(self::$testSurvey))->activate();

        $this->assertSame('OK', $result['status'] ?? null);
        $this->assertSame(['beforeSurveyActivate', 'afterSurveyActivate'], $this->recorder->dispatched);
        $this->assertEquals(
            ['surveyId' => self::$surveyId, 'simulate' => false, 'active' => 'N', 'hasResponseTable' => false],
            $states['beforeSurveyActivate']
        );
        $this->assertEquals(
            ['surveyId' => self::$surveyId, 'simulate' => false, 'active' => 'Y', 'hasResponseTable' => true],
            $states['afterSurveyActivate']
        );
    }

    /**
     * A plugin can stop the activation in beforeSurveyActivate by setting "success" to false.
     * Its message is returned to the caller and afterSurveyActivate is not dispatched.
     *
     * @return void
     */
    public function testPluginCanStopActivation(): void
    {
        $this->recorder->listen('beforeSurveyActivate', function (PluginEvent $event) {
            $event->set('success', false);
            $event->set('message', 'Activation stopped by the test plugin');
        });
        $this->recorder->listen('afterSurveyActivate');

        $result = (new \SurveyActivator(self::$testSurvey))->activate();

        $this->assertSame(['error' => 'plugin', 'message' => 'Activation stopped by the test plugin'], $result);
        $this->assertSame(['beforeSurveyActivate'], $this->recorder->dispatched);
        $this->assertSame('N', $this->getActiveState());
        $this->assertFalse($this->hasResponseTable());
    }

    /**
     * The feedback a plugin sets in beforeSurveyActivate is returned with the activation result.
     *
     * @return void
     */
    public function testPluginFeedbackIsReturnedByActivation(): void
    {
        $this->recorder->listen('beforeSurveyActivate', function (PluginEvent $event) {
            $event->set('pluginFeedback', 'Feedback of the test plugin');
        });

        $result = (new \SurveyActivator(self::$testSurvey))->activate();

        $this->assertSame('OK', $result['status'] ?? null);
        $this->assertSame('Feedback of the test plugin', $result['pluginFeedback']);
    }

    /**
     * Deactivating a survey dispatches beforeSurveyDeactivate while the survey is still active
     * and afterSurveyDeactivate once the survey is inactive and its response table is archived.
     *
     * @return void
     */
    public function testDeactivationDispatchesBeforeAndAfterEvents(): void
    {
        $this->activateSurvey();
        $states = [];
        $recordState = function (PluginEvent $event) use (&$states) {
            $states[$event->getEventName()] = [
                'surveyId' => $event->get('surveyId'),
                'active' => $this->getActiveState(),
                'hasResponseTable' => $this->hasResponseTable(),
            ];
        };
        $this->recorder->listen('beforeSurveyDeactivate', $recordState);
        $this->recorder->listen('afterSurveyDeactivate', $recordState);

        $this->createSurveyDeactivate()->deactivate(self::$surveyId, ['ok' => true], true);

        $this->assertSame(['beforeSurveyDeactivate', 'afterSurveyDeactivate'], $this->recorder->dispatched);
        $this->assertEquals(
            ['surveyId' => self::$surveyId, 'active' => 'Y', 'hasResponseTable' => true],
            $states['beforeSurveyDeactivate']
        );
        $this->assertEquals(
            ['surveyId' => self::$surveyId, 'active' => 'N', 'hasResponseTable' => false],
            $states['afterSurveyDeactivate']
        );
    }

    /**
     * A plugin can stop the deactivation in beforeSurveyDeactivate by setting a message.
     * The message is returned to the caller and afterSurveyDeactivate is not dispatched.
     *
     * @return void
     */
    public function testPluginCanStopDeactivation(): void
    {
        $this->activateSurvey();
        $this->recorder->listen('beforeSurveyDeactivate', function (PluginEvent $event) {
            $event->set('success', false);
            $event->set('message', 'Deactivation stopped by the test plugin');
        });
        $this->recorder->listen('afterSurveyDeactivate');

        $result = $this->createSurveyDeactivate()->deactivate(self::$surveyId, ['ok' => true], true);

        $this->assertSame(
            ['beforeDeactivate' => ['success' => false, 'message' => 'Deactivation stopped by the test plugin']],
            $result
        );
        $this->assertSame(['beforeSurveyDeactivate'], $this->recorder->dispatched);
        $this->assertSame('Y', $this->getActiveState());
        $this->assertTrue($this->hasResponseTable());
    }

    /**
     * Activate the test survey, before the test starts listening to events.
     *
     * @return void
     */
    private function activateSurvey(): void
    {
        $result = (new \SurveyActivator(self::$testSurvey))->activate();
        $this->assertSame('OK', $result['status'] ?? null, 'The test survey could not be activated.');
        // The cached table names do not include the response table created by the activation yet
        App()->db->schema->refresh();
    }

    /**
     * Create the service that deactivates the test survey.
     *
     * @return SurveyDeactivate
     */
    private function createSurveyDeactivate(): SurveyDeactivate
    {
        $survey = Survey::model()->findByPk(self::$surveyId);
        $surveyDeactivate = new SurveyDeactivate(
            $survey,
            \Permission::model(),
            new \SurveyDeactivator($survey),
            App(),
            \SurveyLink::model(),
            \SavedControl::model()
        );
        $surveyDeactivate->setArchivedResponseSettings(\ArchivedTableSettings::model());
        $surveyDeactivate->setArchivedTimingsSettings(\ArchivedTableSettings::model());
        $surveyDeactivate->setArchivedTokenSettings(\ArchivedTableSettings::model());
        return $surveyDeactivate;
    }

    /**
     * Read the active state of the test survey from the database, as the survey models can be cached.
     *
     * @return string "Y" or "N"
     */
    private function getActiveState(): string
    {
        return (string) App()->db->createCommand()
            ->select('active')
            ->from('{{surveys}}')
            ->where('sid = :sid', [':sid' => self::$surveyId])
            ->queryScalar();
    }

    /**
     * Whether the response table of the test survey exists in the database.
     *
     * @return bool
     */
    private function hasResponseTable(): bool
    {
        return App()->db->schema->getTable('{{responses_' . self::$surveyId . '}}', true) !== null;
    }
}
