<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use LimeSurvey\Helpers\Update\Update_700;

/**
 * The database update to version 7 re-creates the response and timings tables from
 * their current definition and copies the data over. The re-created columns must keep
 * their length, precision and collation, otherwise the copy fails or loses data.
 */
class Update700RebuildTableTest extends TestBaseClass
{
    /** @var string Name of the original table, including the table prefix */
    private $oldTable;

    /** @var string Name of the re-created table, including the table prefix */
    private $newTable;

    /**
     * Sets the names of the two test tables.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $prefix = \Yii::app()->db->tablePrefix;
        $this->oldTable = $prefix . 'test_rebuild_old';
        $this->newTable = $prefix . 'test_rebuild_new';
        $this->dropTables();
    }

    /**
     * Removes the test tables.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dropTables();
        parent::tearDown();
    }

    /**
     * Drops both test tables if they exist.
     *
     * @return void
     */
    private function dropTables(): void
    {
        $db = \Yii::app()->db;
        foreach ([$this->oldTable, $this->newTable] as $table) {
            if ($db->schema->getTable($table, true) !== null) {
                $db->createCommand()->dropTable($table);
            }
        }
    }

    /**
     * Returns the case sensitive collation used for the token column of response tables.
     *
     * @return string Collation clause, empty when the driver is case sensitive by default
     */
    private function getTokenCollation(): string
    {
        switch (\Yii::app()->db->driverName) {
            case 'mysql':
            case 'mysqli':
                return " COLLATE 'utf8mb4_bin'";
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                return " COLLATE SQL_Latin1_General_CP1_CS_AS";
            default:
                return '';
        }
    }

    /**
     * Long texts, decimals and case sensitive tokens survive the table rebuild unchanged.
     *
     * @return void
     */
    public function testRebuiltTableKeepsColumnDefinitions(): void
    {
        $db = \Yii::app()->db;
        $db->createCommand()->createTable($this->oldTable, [
            'id' => 'pk',
            'token' => 'string(36)' . $this->getTokenCollation(),
            'startlanguage' => 'string(20) NOT NULL',
            'shortanswer' => 'string(5)',
            'longanswer' => 'text',
            'numericanswer' => 'decimal (30,10)',
        ]);
        $longAnswer = str_repeat('ü', 1000);
        $db->createCommand()->insert($this->oldTable, [
            'token' => 'AbC',
            'startlanguage' => 'en',
            'shortanswer' => 'A1',
            'longanswer' => $longAnswer,
            'numericanswer' => '12345678901234567.1234567891',
        ]);

        $update = new Update_700($db, '');
        $update->doPreparations();
        $createTable = $update->adjustShowCreateTable(
            $db->createCommand($update->showCreateTable($this->oldTable))->queryRow(),
            $this->oldTable
        );
        $db->createCommand(str_replace($this->oldTable, $this->newTable, $createTable['Create Table']))->execute();

        $columns = 'id, token, startlanguage, shortanswer, longanswer, numericanswer';
        $insert = "INSERT INTO {$this->newTable} ({$columns}) SELECT {$columns} FROM {$this->oldTable}";
        if (in_array($db->driverName, ['mssql', 'sqlsrv', 'dblib'])) {
            $insert = "SET IDENTITY_INSERT {$this->newTable} ON; {$insert}; SET IDENTITY_INSERT {$this->newTable} OFF;";
        }
        $db->createCommand($insert)->execute();

        $row = $db->createCommand()->select('*')->from($this->newTable)->queryRow();
        $this->assertSame('A1', $row['shortanswer']);
        $this->assertSame($longAnswer, $row['longanswer'], 'Long text answers must not be truncated.');
        $this->assertSame(
            '12345678901234567.1234567891',
            $row['numericanswer'],
            'Decimal answers must keep their precision.'
        );
        $lowercaseMatches = $db->createCommand()
            ->select('COUNT(*)')
            ->from($this->newTable)
            ->where('token = :token', [':token' => 'abc'])
            ->queryScalar();
        $this->assertEquals(0, $lowercaseMatches, 'The token column must stay case sensitive.');
    }
}
