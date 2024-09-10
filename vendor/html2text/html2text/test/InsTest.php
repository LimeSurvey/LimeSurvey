<?php

namespace Html2Text;

use PHPUnit\Framework\TestCase;

class InsTest extends TestCase
{
    public function testIns()
    {
        $html = 'This is <ins>inserted</ins>';
        $expected = 'This is _inserted_';

        $html2text = new Html2Text($html);
        $this->assertEquals($expected, $html2text->getText());
    }
}
