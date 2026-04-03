<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyActivate;
use LimeSurvey\Models\Services\SurveyDeactivate;
use LimeSurvey\Models\Services\SurveyAccessModeService;

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
            $query = "select {$questions[0]->sid}X{$questions[0]->gid}X{$questions[0]->qid} as first, {$questions[1]->sid}X{$questions[1]->gid}X{$questions[1]->qid} as second, {$questions[2]->sid}X{$questions[2]->gid}X{$questions[2]->qid} as third from {{survey_" . $survey->sid . "}}";
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
            App()->db->createCommand("drop table {{survey_" . $survey->sid . "}}")->execute();
            $questions[1]->encrypted = 'N';
            $questions[1]->save();
            $surveyActivator->activate($survey->sid, ['restore' => true], true);
            $responses3 = App()->db->createCommand($query)->queryAll();
            $this->assertEquals($responses3[0]['first'], $responses1[0]['first']);
            $this->assertEquals($responses3[0]['second'], $responses1[0]['second']);
            $this->assertEquals($responses3[0]['third'], null);
        }
    }
}