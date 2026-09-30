<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests for identifier handling of statistics_helper::_listcolumn() and of the
 * database schema identifier quoting (mantis #20741).
 */
class StatisticsListColumnTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/survey_simple_statistics.lsa');
    }

    /**
     * An identifier containing the quote characters of every supported database
     * must stay a single identifier once quoted.
     */
    public function testQuoteColumnNameEscapesQuoteCharacters()
    {
        $name = 'a`b"c]d';
        $row = Yii::app()->db
            ->createCommand('SELECT 1 AS ' . Yii::app()->db->quoteColumnName($name))
            ->queryRow();

        $this->assertSame([$name], array_keys($row));
    }

    /**
     * Table names are escaped the same way as the column name verified above.
     */
    public function testQuoteTableNameEscapesQuoteCharacters()
    {
        $name = 'a`b"c]d';
        $schema = Yii::app()->db->getSchema();

        $this->assertSame($schema->quoteSimpleColumnName($name), $schema->quoteSimpleTableName($name));
    }

    /**
     * Listing a real response table column works.
     */
    public function testListColumnAcceptsResponseTableColumn()
    {
        $helper = new \statistics_helper();
        $result = $helper->_listcolumn(self::$surveyId, 'id', 'id', 'desc', 'N');

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
    }

    /**
     * A column that is not part of the response table is rejected.
     */
    public function testListColumnRejectsUnknownColumn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $helper = new \statistics_helper();
        $helper->_listcolumn(self::$surveyId, 'id`x');
    }

    /**
     * A sort column that is not part of the response table is rejected.
     */
    public function testListColumnRejectsUnknownSortColumn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $helper = new \statistics_helper();
        $helper->_listcolumn(self::$surveyId, 'id', 'id`x');
    }
}
