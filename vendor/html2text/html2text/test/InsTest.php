<?php

namespace Html2Text;

class InsTest extends \PHPUnit_Framework_TestCase
{
    public function testIns()
    {
        $html = 'This is <ins>inserted</ins>';
        $expected = 'This is _inserted_';

        $html2text = new Html2Text($html);
        $this->assertEquals($expected, $html2text->getText());
    }
}
