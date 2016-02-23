<?php
use Codeception\Util\Stub;

require(__DIR__ . '/../../helpers/TbArray.php');

class TbArrayTest extends TbTestCase
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    public function testGetValue()
    {
        $array = array('key' => 'value');
        $this->assertEquals('value', TbArray::getValue('key', $array));
        $nullValueArray = array('key' => null);
        $this->assertNull(TbArray::getValue('key', $nullValueArray));
        $this->assertNull(TbArray::getValue('key', $nullValueArray, 'not null'), 'Null value has to be found');
    }

    public function testPopValue()
    {
        $array = array('key' => 'value');
        $this->assertEquals('value', TbArray::popValue('key', $array));
        $this->assertArrayNotHasKey('key', $array);
        $nullValueArray = array('key' => null);
        $this->assertNull(TbArray::popValue('key', $nullValueArray, 'not null'), 'Null value has to be found');
        $this->assertArrayNotHasKey('key', $nullValueArray);
    }

    public function testDefaultValue()
    {
        $array = array();
        TbArray::defaultValue('key', 'default', $array);
        $this->assertEquals('default', TbArray::getValue('key', $array));
        TbArray::defaultValue('key', 'value', $array);
        $this->assertEquals('default', TbArray::getValue('key', $array));
    }

    public function testDefaultValues()
    {
        $array = array('my' => 'value');
        TbArray::defaultValues(array('these' => 'are', 'my' => 'defaults'), $array);
        $this->assertEquals('are', TbArray::getValue('these', $array));
        $this->assertEquals('value', TbArray::getValue('my', $array));
    }

    public function testRemoveValue()
    {
        $array = array('key' => 'value');
        TbArray::removeValue('key', $array);
        $this->assertArrayNotHasKey('key', $array);
    }

    public function testRemoveValues()
    {
        $array = array('these' => 'are', 'my' => 'values');
        TbArray::removeValues(array('these', 'my'), $array);
        $this->assertArrayNotHasKey('these', $array);
        $this->assertArrayNotHasKey('my', $array);
    }

    public function testCopyValues()
    {
        $a = array('key' => 'value');
        $b = array();
        $array = TbArray::copyValues(array('key'), $a, $b);
        $this->assertEquals($a, $array);
        $a = array('key' => 'value');
        $b = array('key' => 'other');
        $array = TbArray::copyValues(array('key'), $a, $b, true);
        $this->assertEquals($a, $array);
    }

    public function testMoveValues()
    {
        $a = array('key' => 'value');
        $b = array();
        $array = TbArray::moveValues(array('key'), $a, $b);
        $this->assertArrayNotHasKey('key', $a);
        $this->assertEquals('value', TbArray::getValue('key', $array));
        $a = array('key' => 'value');
        $b = array('key' => 'other');
        $array = TbArray::moveValues(array('key'), $a, $b, true);
        $this->assertEquals('value', TbArray::getValue('key', $array));
    }

    public function testMerge()
    {
        $a = array('this' => 'is', 'array' => 'a');
        $b = array('is' => 'this', 'b' => 'array');
        $array = TbArray::merge($a, $b);
        $this->assertEquals('is', TbArray::getValue('this', $array));
        $this->assertEquals('a', TbArray::getValue('array', $array));
        $this->assertEquals('this', TbArray::getValue('is', $array));
        $this->assertEquals('array', TbArray::getValue('b', $array));
    }
}