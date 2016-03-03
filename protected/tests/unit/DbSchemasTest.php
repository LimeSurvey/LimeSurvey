<?php


namespace ls\tests\unit;


class DbSchemasTest extends \PHPUnit_Framework_TestCase
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
        $this->db = new \CDbConnection('mysql:host=127.0.0.1;dbname=yii', 'root', 'secret');
    }

    /**
     * Test that default column types supported by Yii are still correct in our subclasses.
     * @param $class
     */
    protected function doForClass($class)
    {
        /** @var \CDbSchema $schema */
        $schema = new $class($this->db);
        foreach ($schema->columnTypes as $source => $target) {
            // Code must not mangle defaults from array configuration.
            $this->assertEquals($target, $schema->getColumnType($source));
        }
    }

    public function testMySql()
    {
        $schema = new \MysqlSchema($this->db);
        $this->assertEquals('int(11) DEFAULT 0', $schema->getColumnType('integer DEFAULT 0'));
        $this->doForClass(\MysqlSchema::class);
    }
    public function testMsSql()
    {
        $schema = new \MssqlSchema($this->db);
        $this->assertEquals('int IDENTITY PRIMARY KEY NOT NULL', $schema->getColumnType('pk'));
    }

    public function testPgSql()
    {
        $schema = new \PgsqlSchema($this->db);
        $this->assertEquals('numeric (10,0)', $schema->getColumnType('decimal'));
        $this->assertEquals('character varying(10) NOT NULL', $schema->getColumnType('string(10) NOT NULL'));
        $this->doForClass(\PgsqlSchema::class);
    }

}