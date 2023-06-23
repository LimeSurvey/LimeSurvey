<?php

namespace Html2Text;

class DelTest extends \PHPUnit_Framework_TestCase
{
    public function testDel()
    {
        $html = 'My <del>Résumé</del> Curriculum Vitæ';
        $expected = 'My R̶é̶s̶u̶m̶é̶ Curriculum Vitæ';

        $html2text = new Html2Text($html);
        $this->assertEquals($expected, $html2text->getText());
    }
}
