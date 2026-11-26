<?php

namespace ls\tests\unit\helpers;

use Assessment;
use ls\tests\TestBaseClass;
use QuestionGroup;
use Survey;
use Yii;

/**
 * Tests that assessments keep their group when a survey is exported to and imported from the tab-separated-values format.
 */
class TsvImportExportAssessmentsTest extends TestBaseClass
{
    /** @var string Exported tab-separated-values survey file */
    private static $tsvFile;

    /** @var int|null Survey ID of the survey imported from the tab-separated-values file */
    private static $tsvSurveyId;

    /**
     * Imports a survey with several groups, adds assessments and exports it to a tab-separated-values file.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);
        Yii::import('application.helpers.export_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');

        $groups = QuestionGroup::model()->findAllByAttributes(['sid' => self::$surveyId], ['order' => 'group_order']);
        $assessments = [
            ['scope' => 'G', 'gid' => $groups[0]->gid, 'name' => 'First group assessment'],
            ['scope' => 'G', 'gid' => $groups[1]->gid, 'name' => 'Second group assessment'],
            ['scope' => 'T', 'gid' => 0, 'name' => 'Total assessment'],
        ];
        foreach ($assessments as $assessment) {
            Yii::app()->db->createCommand()->insert(
                '{{assessments}}',
                array_merge($assessment, ['sid' => self::$surveyId, 'minimum' => '0', 'maximum' => '10', 'message' => 'Message', 'language' => 'en'])
            );
        }

        ob_start();
        tsvSurveyExport(self::$surveyId);
        $tsv = ob_get_clean();
        self::$tsvFile = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'limesurvey_survey_tsv_' . uniqid() . '.txt';
        file_put_contents(self::$tsvFile, $tsv);
    }

    /**
     * Removes the exported file and the survey imported from it.
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$tsvFile && file_exists(self::$tsvFile)) {
            unlink(self::$tsvFile);
        }
        if (self::$tsvSurveyId) {
            Yii::app()->session['loginID'] = 1;
            Survey::model()->findByPk(self::$tsvSurveyId)->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * Group assessments are imported into the group they were exported from, total assessments into no group.
     */
    public function testImportKeepsAssessmentGroups()
    {
        Yii::app()->session['loginID'] = 1;
        $results = importSurveyFile(self::$tsvFile, false);
        $this->assertArrayNotHasKey('error', $results);
        self::$tsvSurveyId = $results['newsid'];

        $groupOrders = [];
        foreach (QuestionGroup::model()->findAllByAttributes(['sid' => self::$tsvSurveyId]) as $group) {
            $groupOrders[$group->gid] = (int) $group->group_order;
        }
        $assessments = [];
        foreach (Assessment::model()->findAllByAttributes(['sid' => self::$tsvSurveyId]) as $assessment) {
            $assessments[$assessment->name] = $assessment;
        }

        $this->assertCount(3, $assessments);
        $this->assertSame(1, $groupOrders[$assessments['First group assessment']->gid] ?? null);
        $this->assertSame(2, $groupOrders[$assessments['Second group assessment']->gid] ?? null);
        $this->assertSame(0, (int) $assessments['Total assessment']->gid);
    }
}
