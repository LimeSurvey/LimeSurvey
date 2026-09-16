<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyAccessModeService;
use Survey;
use Permission;

/**
 * @group survey
 */
class AccessModeChangeTest extends TestBaseClass
{
    protected $survey = null;

    protected SurveyAccessModeService $surveyAccessModeService;

    protected function getSurvey($sid = null)
    {
        if (!$this->survey) {
            $this->survey = Survey::model()->findByPk($sid);
        }
        return $this->survey;
    }
    protected function importTheFile($filename)
    {
        $surveyFile = self::$surveysFolder . '/' . $filename;
        $result = importSurveyFile($surveyFile, false);
        $this->assertTrue(is_array($result) && isset($result['newsid']));
        $this->surveyAccessModeService = new SurveyAccessModeService(
            Permission::model(),
            $this->getSurvey($result['newsid']),
            App(),
            true
        );
        return $result;
    }

    /**
     * There are multiple similar-looking tests, this is an abstractization of them to avoid code duplication
     * @param string $filename the name of the file to be imported
     * @param string $initialAccessMode the access mode we expect just after the import
     * @param bool $initialHasTokensTable whether we expect a tokens table to exist just after the import
     * @param string $targetAccessMode the access mode we expect to have after the change
     * @param string $tokenState what token action we expect the change to do
     * @return void
     */
    protected function doTheTest(
        string $filename,
        string $initialAccessMode,
        bool $initialHasTokensTable,
        string $targetAccessMode,
        string $tokenState
    ){
        $result = $this->importTheFile($filename);
        $this->assertTrue($result['access_mode'] === $initialAccessMode);
        $this->assertTrue($this->getSurvey()->hasTokensTable === $initialHasTokensTable);
        $this->surveyAccessModeService->changeAccessMode($this->getSurvey()->sid, $targetAccessMode);
        $this->getSurvey()->refresh();
        $this->assertTrue($this->getSurvey()->access_mode === $targetAccessMode);
        $this->assertTrue($this->surveyAccessModeService->getTokenTableAction() === $tokenState);
    }

    /**
     * Tests
     * - importing a survey
     *     - access_mode: O
     *     - tokens table: nonexistent
     * - changes access mode to C
     * - expects
     *     - initial access_mode: O
     *     - initial tokens table: nonexistent
     *     - access_mode to become: C
     *     - tokens table to be created
     * @return void
     */
    public function testAccessModeChangeOpenToClosedCreatingTokensTable()
    {
        $this->doTheTest(
            'access_modes_797496.lsa',
            SurveyAccessModeService::$ACCESS_TYPE_OPEN,
            false,
            SurveyAccessModeService::$ACCESS_TYPE_CLOSED,
            SurveyAccessModeService::$TOKEN_TABLE_CREATED
        );
    }

    /**
     * Tests
     * - importing a survey
     *     - access_mode: C
     *     - tokens table: existent and empty
     * - changes access mode to O
     * - expects
     *     - initial access_mode: C
     *     - initial tokens table: existent, empty
     *     - access_mode to become: O
     *     - tokens table to be dropped
     * @return void
     */
    public function testAccessModeChangeClosedToOpenDroppingTokensTable()
    {
        $this->doTheTest(
            'access_modes_368399.lsa',
            SurveyAccessModeService::$ACCESS_TYPE_CLOSED,
            true,
            SurveyAccessModeService::$ACCESS_TYPE_OPEN,
            SurveyAccessModeService::$TOKEN_TABLE_DROPPED
        );
    }

    /**
     * Tests
     * - importing a survey
     *     - access_mode: O
     *     - tokens table: existent and has data
     * - changes access mode to C
     * - expects
     *     - initial access_mode: O
     *     - initial tokens table: existent with data
     *     - access_mode to become: C
     *     - tokens table to be not touched
     * @return void
     */
    public function testAccessModeChangeOpenToClosedKeepingTokensTable()
    {
        $this->doTheTest(
            'access_modes_976917.lsa',
            SurveyAccessModeService::$ACCESS_TYPE_OPEN,
            true,
            SurveyAccessModeService::$ACCESS_TYPE_CLOSED,
            SurveyAccessModeService::$TOKEN_TABLE_NO_ACTION
        );
    }

    /**
     * Tests
     * - importing a survey
     *     - access_mode: C
     *     - tokens table: existent and has data
     * - changes access mode to O
     * - expects
     *     - initial access_mode: C
     *     - initial tokens table: existent with data
     *     - access_mode to become: O
     *     - tokens table to be not touched
     * @return void
     */
    public function testAccessModeChangeClosedToOpenKeepingTokensTable()
    {
        $this->doTheTest(
            'access_modes_553745.lsa',
            SurveyAccessModeService::$ACCESS_TYPE_CLOSED,
            true,
            SurveyAccessModeService::$ACCESS_TYPE_OPEN,
            SurveyAccessModeService::$TOKEN_TABLE_NO_ACTION
        );
    }

    /**
     * Regression test for issue #20457: Yii's schema getTableNames() result (used by
     * hasTokensTable/tableExists) is cached for the whole request. If something reads
     * hasTokensTable before the tokens table is created and Token::createTable() does not
     * invalidate that cache, a later hasTokensTable check in the same request keeps
     * reporting the table as missing, causing a redundant/failing second create attempt
     * (this is what happens when SurveyActivate::activate() creates the table and
     * SurveyAccessModeService::changeAccessMode() re-checks it right after, within the
     * same HTTP request).
     * @return void
     */
    public function testHasTokensTableReflectsTableCreatedWithinSameRequest()
    {
        $result = $this->importTheFile('access_modes_797496.lsa');
        $survey = $this->getSurvey($result['newsid']);
        $this->assertFalse($survey->hasTokensTable);

        // Prime the schema's table-name cache the way an earlier hasTokensTable
        // check within the same request would, before the tokens table exists.
        \Yii::app()->db->schema->getTableNames();

        \Token::createTable($survey->sid);

        $this->assertTrue(
            $survey->hasTokensTable,
            'hasTokensTable must reflect a table created earlier in the same request, not a stale cached table list.'
        );
    }
}