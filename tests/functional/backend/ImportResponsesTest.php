<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyActivate;
use LimeSurvey\Models\Services\SurveyDeactivate;
use LimeSurvey\Models\Services\SurveyAccessModeService;

/**
 * @group import-export
 */
class ImportResponsesTest extends TestBaseClass
{
    
    public function testResponseImport()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_969899_ImportResponses.lsa';
        $result = importSurveyFile($surveyFile, false);
        $this->assertTrue($importSuccess = (is_array($result) && isset($result['newsid'])));
        if ($importSuccess) {
            $survey = \Survey::model()->findByPk($result['newsid']);
            $questions = \Question::model()->findAll(
                'sid = :sid',
                [':sid' => $result['newsid']]
            );
            $query = "select Q{$questions[0]->qid} as first, Q{$questions[1]->qid} as second, Q{$questions[2]->qid} as third from {{responses_" . $survey->sid . "}}";
            $permission = \Permission::model();
            $surveyLink = \SurveyLink::model();
            $savedControl = \SavedControl::model();
            $surveyDeactivator = new SurveyDeactivate(
                $survey,
                $permission,
                new \SurveyDeactivator($survey),
                App(),
                $surveyLink,
                $savedControl
            );
            $surveyDeactivator->setArchivedResponseSettings(\ArchivedTableSettings::model());
            $surveyDeactivator->setArchivedTimingsSettings(\ArchivedTableSettings::model());
            $surveyDeactivator->setArchivedTokenSettings(\ArchivedTableSettings::model());
            $responses1 = App()->db->createCommand($query)->queryAll();
            $surveyDeactivator->deactivate($survey->sid, ['ok' => true], true);
            $questions[1]->encrypted = 'Y';
            $questions[1]->save();
            $questions[2]->type = \Question::QT_D_DATE;
            $questions[2]->save();
            $surveyActivator = new SurveyActivate(
                $survey,
                $permission,
                new \SurveyActivator($survey),
                App(),
                new SurveyAccessModeService(
                    $permission,
                    $survey,
                    App()
                )
            );
            $surveyActivator->activate($survey->sid, ['restore' => true], true);
            $responses2 = App()->db->createCommand($query)->queryAll();
            $this->assertEquals($responses2[0]['first'], $responses1[0]['first']);
            $this->assertNotEquals($responses2[0]['second'], $responses1[0]['second']);
            $this->assertEquals($responses2[0]['third'], null);
            $survey = \Survey::model()->findByPk($result['newsid']);
            $surveyDeactivator->deactivate($survey->sid, ['ok' => true], true);
            $questions[1]->encrypted = 'N';
            $questions[1]->save();
            $surveyActivator->activate($survey->sid, ['restore' => true], true);
            $responses3 = App()->db->createCommand($query)->queryAll();
            $this->assertEquals($responses3[0]['first'], $responses1[0]['first']);
            $this->assertEquals($responses3[0]['second'], $responses1[0]['second']);
            $this->assertEquals($responses3[0]['third'], null);
        }
    }

    /**
     * Restoring archived responses on activation must dispatch beforeDataEntryImport
     * once per restored response, after it was written, like the other response imports.
     */
    public function testResponseRestoreDispatchesImportEventAfterInsert()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_969899_ImportResponses.lsa';
        $result = importSurveyFile($surveyFile, false);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('newsid', $result);
        $surveyId = $result['newsid'];
        $survey = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new SurveyDeactivate(
            $survey,
            $permission,
            new \SurveyDeactivator($survey),
            App(),
            \SurveyLink::model(),
            \SavedControl::model()
        );
        $surveyDeactivator->setArchivedResponseSettings(\ArchivedTableSettings::model());
        $surveyDeactivator->setArchivedTimingsSettings(\ArchivedTableSettings::model());
        $surveyDeactivator->setArchivedTokenSettings(\ArchivedTableSettings::model());
        $archivedCount = (int) App()->db->createCommand("select count(*) from {{responses_$surveyId}}")->queryScalar();
        $this->assertGreaterThan(0, $archivedCount);
        $surveyDeactivator->deactivate($surveyId, ['ok' => true], true);

        $pluginManager = App()->getPluginManager();
        $listener = new class ($pluginManager, 'importEventListener') extends \PluginBase {
            /** @var array Per event: The survey ID, the response row count at dispatch time and the model */
            public $events = [];

            /**
             * Records the event together with the number of responses already in the table
             * @return void
             */
            public function beforeDataEntryImport()
            {
                $surveyId = $this->getEvent()->get('iSurveyID');
                $this->events[] = [
                    'surveyId' => $surveyId,
                    'rowCount' => (int) App()->db->createCommand("select count(*) from {{responses_$surveyId}}")->queryScalar(),
                    'model' => $this->getEvent()->get('oModel'),
                ];
            }
        };
        $pluginManager->subscribe($listener, 'beforeDataEntryImport');
        try {
            $surveyActivator = new SurveyActivate(
                $survey,
                $permission,
                new \SurveyActivator($survey),
                App(),
                new SurveyAccessModeService($permission, $survey, App())
            );
            $surveyActivator->activate($surveyId, ['restore' => true], true);
        } finally {
            $pluginManager->unsubscribe($listener, 'beforeDataEntryImport');
        }

        $this->assertCount($archivedCount, $listener->events);
        foreach ($listener->events as $index => $event) {
            $this->assertEquals($surveyId, $event['surveyId']);
            // The response is already written when the event is dispatched
            $this->assertGreaterThan($index, $event['rowCount']);
            // The model carries the restored values, not only the question answers
            $this->assertNotEmpty($event['model']->startlanguage);
        }
    }
}
