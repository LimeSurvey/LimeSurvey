<?php

namespace ls\tests;

use LimeSurvey\Helpers\Update\Update_722;

/**
 * DB update 722 adds or repairs the welcome_image column for installations that passed DB version 718 while it was
 * still empty
 */
class WelcomeImageColumnUpdateTest extends TestBaseClass
{
    /** @var string The surveys table name with prefix */
    private static $table;

    /** @var string|false DBVersion before the test : Update_722::safeUp() sets it to 722 */
    private static $dbVersion;

    /**
     * Imports a survey, so the surveys table has a row whose value must survive the conversion
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.update.updatedb_helper', true);
        self::$table = \Yii::app()->db->tablePrefix . 'surveys';
        self::$dbVersion = \Yii::app()->db->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where("stg_name = 'DBVersion'")
            ->queryScalar();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_188432_answerSetsForEmTest.lss');
    }

    /**
     * Restores DBVersion, the next runs on the same database (debug mode in CI) read it
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$dbVersion !== false) {
            \Yii::app()->db->createCommand()->update(
                '{{settings_global}}',
                ['stg_value' => self::$dbVersion],
                "stg_name = 'DBVersion'"
            );
        }
        parent::tearDownAfterClass();
    }

    /**
     * Restores the column of a fresh installation after each test
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->replaceColumn(null);
        (new Update_722(\Yii::app()->db, []))->safeUp();
        \Survey::model()->refreshMetaData();
        parent::tearDown();
    }

    /**
     * The update adds the missing column, and running it again changes nothing
     *
     * @return void
     */
    public function testAddsMissingColumn()
    {
        $this->replaceColumn(null);
        $this->assertNull($this->findColumn());

        (new Update_722(\Yii::app()->db, []))->safeUp();
        $this->assertExpectedColumn();

        (new Update_722(\Yii::app()->db, []))->safeUp();
        $this->assertExpectedColumn();
    }

    /**
     * A column added by hand with another name case, a short type and NOT NULL is converted, its values are kept
     *
     * @return void
     */
    public function testConvertsColumnAddedByHand()
    {
        $this->replaceColumn('Welcome_Image', "string(255) NOT NULL DEFAULT ''");
        $value = '{"image_path":"welcome.png"}';
        \Yii::app()->db->createCommand()->update(
            self::$table,
            ['Welcome_Image' => $value],
            'sid = :sid',
            [':sid' => self::$testSurvey->sid]
        );

        (new Update_722(\Yii::app()->db, []))->safeUp();

        $this->assertExpectedColumn();
        $this->assertSame($value, $this->getWelcomeImage());
    }

    /**
     * A text column added by hand keeps its values, and gets the type of a fresh installation
     *
     * @return void
     */
    public function testKeepsValuesOfTextColumnAddedByHand()
    {
        $this->replaceColumn('welcome_image', 'text NULL');
        $value = '{"image_path":"welcome.png"}';
        \Yii::app()->db->createCommand()->update(
            self::$table,
            ['welcome_image' => $value],
            'sid = :sid',
            [':sid' => self::$testSurvey->sid]
        );

        (new Update_722(\Yii::app()->db, []))->safeUp();

        $this->assertExpectedColumn();
        $this->assertSame($value, $this->getWelcomeImage());
    }

    /**
     * Drops the welcome_image column in any name case, and adds the given one instead
     *
     * @param string|null $name The column name to add, null to only drop it
     * @param string $type The abstract column type
     * @return void
     */
    private function replaceColumn(?string $name, string $type = 'text NULL'): void
    {
        $column = $this->findColumn();
        if ($column !== null) {
            dropColumn(self::$table, $column->name);
        }
        if ($name !== null) {
            addColumn(self::$table, $name, $type);
        }
        \Yii::app()->db->schema->getTable(self::$table, true);
    }

    /**
     * Returns the welcome_image column in any name case
     *
     * @return \CDbColumnSchema|null
     */
    private function findColumn(): ?\CDbColumnSchema
    {
        foreach (\Yii::app()->db->schema->getTable(self::$table, true)->columns as $column) {
            if (strcasecmp($column->name, 'welcome_image') === 0) {
                return $column;
            }
        }
        return null;
    }

    /**
     * Asserts that the column has the name, type and nullability of a fresh installation
     *
     * @return void
     */
    private function assertExpectedColumn(): void
    {
        $column = $this->findColumn();
        $this->assertNotNull($column, 'The welcome_image column must exist');
        $this->assertSame('welcome_image', $column->name);
        $this->assertTrue($column->allowNull, 'The welcome_image column must allow NULL');
        $this->assertSame('string', $column->type);
        // MSSQL reports nvarchar(max) with the size -1
        $this->assertContains($column->size, [null, -1], 'The welcome_image column must not have a length limit');
    }

    /**
     * Returns the welcome_image value of the test survey
     *
     * @return string|null
     */
    private function getWelcomeImage(): ?string
    {
        $value = \Yii::app()->db->createCommand()
            ->select('welcome_image')
            ->from(self::$table)
            ->where('sid = :sid', [':sid' => self::$testSurvey->sid])
            ->queryScalar();
        return $value === false ? null : $value;
    }
}
