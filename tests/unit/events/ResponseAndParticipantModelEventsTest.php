<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginEvent;

/**
 * Tests the plugin events dispatched when a response or a survey participant is saved or deleted
 * through its model: before/afterResponseSave, before/afterResponseDelete, before/afterTokenSave,
 * before/afterTokenDelete, and the generic before/afterModelSave and before/afterModelDelete.
 */
class ResponseAndParticipantModelEventsTest extends TestBaseClass
{
    /** @var \EventRecorderPlugin */
    private $recorder;

    /**
     * Import a survey, activate it and create its participant table.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        $result = (new \SurveyActivator(self::$testSurvey))->activate();
        self::assertSame('OK', $result['status'] ?? null, 'The test survey could not be activated.');
        \Token::createTable(self::$surveyId);
        // The cached table names do not include the new tables yet
        App()->db->schema->refresh();
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        require_once self::$dataFolder . '/plugins/EventRecorderPlugin.php';
        $this->recorder = new \EventRecorderPlugin(App()->getPluginManager(), 0);
    }

    /**
     * Remove the event subscriptions.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->recorder->stopListening();
        parent::tearDown();
    }

    /**
     * The models to test.
     *
     * @return array<string, array{0: class-string, 1: string, 2: string}> Model class, name of its table without
     *                                                                     the survey ID, attribute changed by the plugin
     */
    public function modelProvider(): array
    {
        return [
            'response' => ['Response', 'responses', 'startlanguage'],
            'participant' => ['Token', 'tokens', 'lastname'],
        ];
    }

    /**
     * Saving a model dispatches its before and after save events and the generic model events.
     * The events give the model and the survey ID, the model has its ID in the after events,
     * and a change the plugin makes to the model in the before event is saved.
     *
     * @dataProvider modelProvider
     * @param class-string $modelClass
     * @param string $table
     * @param string $attribute
     * @return void
     */
    public function testSaveDispatchesSaveEvents($modelClass, $table, $attribute): void
    {
        $model = $modelClass::create(self::$surveyId);
        $model->setAttribute($attribute, 'en');
        $received = [];
        $record = function (PluginEvent $event) use ($model, &$received) {
            // The generic model events are dispatched for every model
            if ($event->get('model') !== $model) {
                return;
            }
            $received[] = [
                'event' => $event->getEventName(),
                'surveyId' => $event->get('surveyId'),
                'iSurveyID' => $event->get('iSurveyID'),
                'dynamicId' => $event->get('dynamicId'),
                'hasId' => !empty($model->getPrimaryKey()),
            ];
        };
        $this->recorder->listen("before{$modelClass}Save", function (PluginEvent $event) use ($record, $attribute) {
            $record($event);
            $event->get('model')->setAttribute($attribute, 'de');
        });
        $this->recorder->listen('beforeModelSave', $record);
        $this->recorder->listen("after{$modelClass}Save", $record);
        $this->recorder->listen('afterModelSave', $record);

        $this->assertTrue($model->save(), 'The model could not be saved: ' . json_encode($model->getErrors()));

        $surveyId = self::$surveyId;
        $this->assertEquals(
            [
                $this->expectedEvent("before{$modelClass}Save", $surveyId, true, false),
                $this->expectedEvent('beforeModelSave', $surveyId, false, false),
                $this->expectedEvent("after{$modelClass}Save", $surveyId, true, true),
                $this->expectedEvent('afterModelSave', $surveyId, false, true),
            ],
            $received
        );
        $this->assertNotEmpty($model->getPrimaryKey());
        $this->assertSame('de', $this->readAttribute($table, $model->getPrimaryKey(), $attribute));
    }

    /**
     * Deleting a model dispatches its before delete event while the record still exists,
     * its after delete event once the record is gone, and the generic model events.
     *
     * @dataProvider modelProvider
     * @param class-string $modelClass
     * @param string $table
     * @param string $attribute
     * @return void
     */
    public function testDeleteDispatchesDeleteEvents($modelClass, $table, $attribute): void
    {
        $model = $modelClass::create(self::$surveyId);
        $model->setAttribute($attribute, 'en');
        $this->assertTrue($model->save(), 'The model could not be saved: ' . json_encode($model->getErrors()));
        $id = $model->getPrimaryKey();
        $received = [];
        $record = function (PluginEvent $event) use ($model, $table, $attribute, $id, &$received) {
            if ($event->get('model') !== $model) {
                return;
            }
            $received[] = [
                'event' => $event->getEventName(),
                'surveyId' => $event->get('surveyId'),
                'dynamicId' => $event->get('dynamicId'),
                'recordExists' => $this->readAttribute($table, $id, $attribute) !== null,
            ];
        };
        $this->recorder->listen("before{$modelClass}Delete", $record);
        $this->recorder->listen('beforeModelDelete', $record);
        $this->recorder->listen("after{$modelClass}Delete", $record);
        $this->recorder->listen('afterModelDelete', $record);

        $this->assertTrue($model->delete());

        $surveyId = self::$surveyId;
        $this->assertEquals(
            [
                ['event' => "before{$modelClass}Delete", 'surveyId' => $surveyId, 'dynamicId' => $surveyId, 'recordExists' => true],
                ['event' => 'beforeModelDelete', 'surveyId' => $surveyId, 'dynamicId' => null, 'recordExists' => true],
                ['event' => "after{$modelClass}Delete", 'surveyId' => $surveyId, 'dynamicId' => $surveyId, 'recordExists' => false],
                ['event' => 'afterModelDelete', 'surveyId' => $surveyId, 'dynamicId' => null, 'recordExists' => false],
            ],
            $received
        );
    }

    /**
     * The data a save event is expected to give to a plugin.
     *
     * @param string $eventName
     * @param int $surveyId
     * @param bool $hasDynamicId Whether the event tells the survey ID in "dynamicId" too
     * @param bool $hasId Whether the model has its ID when the event is dispatched
     * @return array<string, mixed>
     */
    private function expectedEvent($eventName, $surveyId, $hasDynamicId, $hasId): array
    {
        return [
            'event' => $eventName,
            'surveyId' => $surveyId,
            'iSurveyID' => $surveyId,
            'dynamicId' => $hasDynamicId ? $surveyId : null,
            'hasId' => $hasId,
        ];
    }

    /**
     * Read an attribute of a response or participant from the database.
     *
     * @param string $table Name of the table without the survey ID: "responses" or "tokens"
     * @param int|string $id ID of the response or participant
     * @param string $attribute
     * @return string|null Null if the record does not exist
     */
    private function readAttribute($table, $id, $attribute): ?string
    {
        $idColumn = $table === 'tokens' ? 'tid' : 'id';
        $value = App()->db->createCommand()
            ->select($attribute)
            ->from('{{' . $table . '_' . self::$surveyId . '}}')
            ->where("$idColumn = :id", [':id' => $id])
            ->queryScalar();
        return $value === false ? null : (string) $value;
    }
}
