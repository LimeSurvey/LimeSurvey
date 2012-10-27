<?php
class common_helperTest extends CTestCase
{
    public function setUp()
    {
        parent::setUp();

        Yii::import('application.helpers.*');
    }

    /**
     * @dataProvides
     */
    public function testsubval_sort() {
        $inArray = array(
            array('a' => 'cc', 'b'=>'aa'),
            array('a' => 'bb', 'b'=>'dd')
            );
        $expected = array(
            array('a' => 'cc', 'b'=>'aa'),
            array('a' => 'bb', 'b'=>'dd')            
            );

        $actual = common_helper::subval_sort($inArray, 'b', 'asc');
        $this->assertEquals($expected, $actual);

        $expected = array(
            array('a' => 'bb', 'b'=>'dd'),
            array('a' => 'cc', 'b'=>'aa')
            );

        $actual = common_helper::subval_sort($inArray, 'b');
        $this->assertEquals($expected, $actual);
    }
}