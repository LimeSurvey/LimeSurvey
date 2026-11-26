<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test flattenText function.
 */
class FlattenTextTest extends TestBaseClass
{
    /**
     * The content of <style> blocks must be removed together with the tags.
     */
    public function testStyleBlockContentIsRemoved()
    {
        $text = "<style type=\"text/css\">.test_class {display: hidden;}\n</style>\nThis is a <b>test</b> with inline CSS";
        $this->assertSame('This is a test with inline CSS', flattenText($text));
    }

    /**
     * Several style blocks, also with uppercase tags, are removed without touching the text in between.
     */
    public function testMultipleStyleBlocksAreRemoved()
    {
        $text = '<STYLE>.a {color: red;}</STYLE>First<style media="screen">.b {color: blue;}</style> second';
        $this->assertSame('First second', flattenText($text));
    }

    /**
     * Style blocks are also removed when <span> tags are kept.
     */
    public function testStyleBlockContentIsRemovedWhenKeepingSpan()
    {
        $text = '<style>.a {color: red;}</style><span class="em-var">Text</span>';
        $this->assertSame('<span class="em-var">Text</span>', flattenText($text, true));
    }
}
