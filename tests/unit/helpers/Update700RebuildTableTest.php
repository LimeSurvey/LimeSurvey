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
     * Removes the test tables and the helper function Update_700::doPreparations() creates on PostgreSQL.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dropTables();
        if (\Yii::app()->db->driverName === 'pgsql') {
            \Yii::app()->db->createCommand('DROP FUNCTION IF EXISTS show_create_table(text, text)')->execute();
        }
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
     * Returns the case sensitive collation that survey activation uses for the token column of response tables.
     *
     * @return string Collation clause, empty when the driver is case sensitive by default
     */
    private function getTokenCollation(): string
    {
        $method = new \ReflectionMethod(\SurveyActivator::class, 'getCollation');
        $method->setAccessible(true);
        return $method->invoke(new \SurveyActivator());
    }

    /**
     * Returns the type, length, precision and scale of every column of a table.
     *
     * @param string $table Name of the table, including the table prefix
     * @return array Column definitions, indexed by column name
     */
    private function describeColumns(string $table): array
    {
        $definitions = [];
        foreach (\Yii::app()->db->schema->getTable($table, true)->columns as $name => $column) {
            $definitions[$name] = [
                'dbType' => $column->dbType,
                'size' => $column->size,
                'precision' => $column->precision,
                'scale' => $column->scale,
            ];
        }
        return $definitions;
    }

    /**
     * Long texts, decimals, legacy varchar columns and case sensitive tokens survive the table rebuild unchanged.
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
            // Older installations can still have plain varchar columns in their response tables
            'legacyanswer' => 'varchar(50)',
        ]);
        $longAnswer = str_repeat('ü', 1000);
        $db->createCommand()->insert($this->oldTable, [
            'token' => 'AbC',
            'startlanguage' => 'en',
            'shortanswer' => 'A1',
            'longanswer' => $longAnswer,
            'numericanswer' => '12345678901234567.1234567891',
            'legacyanswer' => 'Legacy varchar answer',
        ]);

        $update = new Update_700($db, '');
        $update->doPreparations();
        $createTable = $update->adjustShowCreateTable(
            $db->createCommand($update->showCreateTable($this->oldTable))->queryRow(),
            $this->oldTable
        );
        $db->createCommand(str_replace($this->oldTable, $this->newTable, $createTable['Create Table']))->execute();

        $this->assertSame(
            $this->describeColumns($this->oldTable),
            $this->describeColumns($this->newTable),
            'The re-created table must keep the type, length, precision and scale of every column.'
        );

        $columns = 'id, token, startlanguage, shortanswer, longanswer, numericanswer, legacyanswer';
        $insert = "INSERT INTO {$this->newTable} ({$columns}) SELECT {$columns} FROM {$this->oldTable}";
        if (in_array($db->driverName, ['mssql', 'sqlsrv', 'dblib'])) {
            $insert = "SET IDENTITY_INSERT {$this->newTable} ON; {$insert}; SET IDENTITY_INSERT {$this->newTable} OFF;";
        }
        $db->createCommand($insert)->execute();

        $row = $db->createCommand()->select('*')->from($this->newTable)->queryRow();
        $this->assertSame('A1', $row['shortanswer']);
        $this->assertSame('Legacy varchar answer', $row['legacyanswer'], 'Varchar answers must not be truncated.');
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
