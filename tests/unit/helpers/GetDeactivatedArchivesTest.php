<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that getDeactivatedArchives() only finds the archive tables of the given survey.
 */
class GetDeactivatedArchivesTest extends TestBaseClass
{
    /** @var string[] Tables created by the test, without the table prefix */
    private $tables = [];

    /**
     * Loads the import helper.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);
    }

    /**
     * Drops the tables created by the test.
     */
    public function tearDown(): void
    {
        foreach ($this->tables as $table) {
            Yii::app()->db->createCommand()->dropTable("{{{$table}}}");
        }
        $this->tables = [];
        Yii::app()->db->schema->refresh();
        parent::tearDown();
    }

    /**
     * Archive tables of other surveys whose name contains the survey ID are not returned.
     */
    public function testOnlyArchivesOfTheSurveyAreFound()
    {
        $sid = 123456;
        $ownTables = [
            'old_responses_123456_20260101000000',
            'old_responses_123456_20260102000000',
            'old_questions_123456_20260101000000',
            'old_tokens_123456_20260101000000',
        ];
        $otherTables = [
            // The survey ID is part of the timestamp
            'old_responses_999999_20261234560000',
            // The survey ID is part of a longer survey ID
            'old_timings_1234567_20260101000000',
            'old_questions_9123456_20260101000000',
        ];
        foreach (array_merge($ownTables, $otherTables) as $table) {
            Yii::app()->db->createCommand()->createTable("{{{$table}}}", ['id' => 'pk']);
            $this->tables[] = $table;
        }

        $prefix = Yii::app()->db->tablePrefix;
        $this->assertSame(
            [
                'questions' => "{$prefix}old_questions_123456_20260101000000",
                'responses' => "{$prefix}old_responses_123456_20260101000000,{$prefix}old_responses_123456_20260102000000",
                'tokens' => "{$prefix}old_tokens_123456_20260101000000",
            ],
            $this->sortByKey(getDeactivatedArchives($sid))
        );
    }

    /**
     * Sorts an array by its keys, as the order of the kinds does not matter.
     *
     * @param array $archives
     * @return array
     */
    private function sortByKey(array $archives): array
    {
        ksort($archives);
        return $archives;
    }
}
