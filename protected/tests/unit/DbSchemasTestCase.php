<?php


namespace ls\tests\unit;


class DbSchemasTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CDbConnection
     */
    private $db;
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->db = new \CDbConnection('mysql:host=127.0.0.1;dbname=yii','test','test');
    }

    /**
     * Test that default column types supported by Yii are still correct in our subclasses.
     * @param $class
     */
    protected function doForClass($class) {
        /** @var \CDbSchema $schema */
        $schema = new $class($this->db);
        $parentSchema = get_parent_class($schema);
        /** @var \CDbSchema $schema2 */
        $schema2 = new $parentSchema($this->db);

        foreach($schema2->columnTypes as $source => $target) {
            $this->assertEquals($target, $schema->getColumnType($source));
        }
    }

    public function testMySql() {
        $this->doForClass(\MysqlSchema::class);
    }
    public function testMsSql() {
        $this->doForClass(\MssqlSchema::class);
    }

    public function testPgSql() {
        $this->doForClass(\PgsqlSchema::class);
    }

}