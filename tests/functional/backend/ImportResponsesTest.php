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
            // Quoted, as PostgreSQL would look for lowercase column names otherwise
            $db = App()->db;
            $query = "select " . $db->quoteColumnName("Q{$questions[0]->qid}") . " as first, "
                . $db->quoteColumnName("Q{$questions[1]->qid}") . " as second, "
                . $db->quoteColumnName("Q{$questions[2]->qid}") . " as third from {{responses_" . $survey->sid . "}}";
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
     * Restoring archived responses on activation must restore the answers of a subquestion that was
     * deleted and re-created with the same code while the survey was deactivated.
     */
    public function testResponseRestoreOfRecreatedSubquestion()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_932686_multiple_choice_empty_values.lss';
        $result = importSurveyFile($surveyFile, false);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('newsid', $result);
        $surveyId = $result['newsid'];
        $survey = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyActivator = new SurveyActivate(
            $survey,
            $permission,
            new \SurveyActivator($survey),
            App(),
            new SurveyAccessModeService($permission, $survey, App())
        );
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

        $question = \Question::model()->findByAttributes(['sid' => $surveyId, 'parent_qid' => 0, 'title' => 'Select']);
        $subquestions = [];
        foreach ($question->subquestions as $subquestion) {
            $subquestions[$subquestion->title] = $subquestion;
        }
        $recreatedColumn = "Q{$question->qid}_S{$subquestions['SQ001']->qid}";
        $keptColumn = "Q{$question->qid}_S{$subquestions['SQ002']->qid}";

        $activation = $surveyActivator->activate($surveyId, [], true);
        $this->assertEmpty($activation['error'] ?? null);
        // Activation and deactivation normally run in separate requests
        App()->db->schema->refresh();
        App()->db->createCommand()->insert("{{responses_$surveyId}}", [
            'startlanguage' => 'en',
            'startdate' => '2026-01-01 00:00:00',
            'datestamp' => '2026-01-01 00:00:00',
            $recreatedColumn => 'Y',
            $keptColumn => 'Y',
        ]);
        $deactivation = $surveyDeactivator->deactivate($surveyId, ['ok' => true], true);
        $archivedResponseTable = $deactivation['aData']['sNewSurveyTableName'];
        $archivedQuestionTable = str_replace('old_responses_', 'old_questions_', $archivedResponseTable);

        // Make the archive look like SQ001 was deleted and re-created with the same code after the deactivation:
        // The archive holds a subquestion ID that no longer exists. Deleting and re-creating it here is not possible,
        // as the subquestions are cached per request (getSubQuestions()).
        $deletedQid = (int) App()->db->createCommand('SELECT MAX(qid) FROM {{questions}}')->queryScalar() + 1;
        $deletedColumn = "Q{$question->qid}_S{$deletedQid}";
        App()->db->createCommand()->renameColumn($archivedResponseTable, $recreatedColumn, $deletedColumn);
        // Delete and insert again, as qid is an identity column on MSSQL that can't be updated
        $archivedSubquestion = App()->db->createCommand()
            ->select('*')
            ->from($archivedQuestionTable)
            ->where('qid = :subquestionqid', [':subquestionqid' => $subquestions['SQ001']->qid])
            ->queryRow();
        App()->db->createCommand()->delete($archivedQuestionTable, 'qid = :subquestionqid', [':subquestionqid' => $subquestions['SQ001']->qid]);
        $archivedSubquestion['qid'] = $deletedQid;
        $unprefixedArchivedQuestionTable = substr($archivedQuestionTable, strlen(App()->db->tablePrefix));
        switchMSSQLIdentityInsert($unprefixedArchivedQuestionTable, true);
        App()->db->createCommand()->insert($archivedQuestionTable, $archivedSubquestion);
        switchMSSQLIdentityInsert($unprefixedArchivedQuestionTable, false);
        App()->db->schema->refresh();

        $activation = $surveyActivator->activate($surveyId, ['restore' => true], true);
        $this->assertTrue($activation['restored']);
        $responses = App()->db->createCommand()
            ->select([$recreatedColumn, $keptColumn])
            ->from("{{responses_$surveyId}}")
            ->queryAll();
        $this->assertCount(1, $responses);
        $this->assertEquals('Y', $responses[0][$recreatedColumn]);
        $this->assertEquals('Y', $responses[0][$keptColumn]);
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
