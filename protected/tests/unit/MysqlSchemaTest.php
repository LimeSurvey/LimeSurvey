<?php


namespace ls\tests\unit;


class MysqlSchemasTest extends \PHPUnit_Framework_TestCase
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

    public function testCreateDropDatabase()
    {
        $schema = new \MysqlSchema($this->db);
        $this->assertTrue($schema->createDatabase('testcreation'));
        $this->assertContains('testcreation', $schema->getDatabases());
        $this->assertTrue($schema->dropDatabase('testcreation'));
        $this->assertNotContains('testcreation', $schema->getDatabases());
    }

}