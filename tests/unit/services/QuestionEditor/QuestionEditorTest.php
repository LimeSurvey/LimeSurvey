<?php

namespace ls\tests\unit\services\QuestionEditor;

use ls\tests\TestBaseClass;

class QuestionEditorTest extends TestBaseClass
{
    public function testTest()
    {
        $questionEditor = (new QuestionEditorFactory)->make();
        $this->assertTrue(true);
    }
}
