<?php

namespace Html2Text;

use PHPUnit\Framework\TestCase;

class ConstructorTest extends TestCase
{
    public function testConstructor()
    {
        $html = 'Foo';
        $options = array('do_links' => 'none');
        $html2text = new Html2Text($html, $options);
        $this->assertEquals($html, $html2text->getText());

        $html2text = new Html2Text($html);
        $this->assertEquals($html, $html2text->getText());
    }

    public function testLegacyConstructor()
    {
        $html = 'Foo';
        $options = array('do_links' => 'none');

        $html2text = new Html2Text($html, false, $options);
        $this->assertEquals($html, $html2text->getText());
    }

    public function testLegacyConstructorThrowsExceptionWhenFromFileIsTrue()
    {
        $html = 'Foo';
        $options = array('do_links' => 'none');

        method_exists($this, 'expectException') 
            ? $this->expectException('InvalidArgumentException')
            : $this->setExpectedException('InvalidArgumentException');

        $html2text = new Html2Text($html, true, $options);
    }
}
