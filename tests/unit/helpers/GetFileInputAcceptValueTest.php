<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test getFileInputAcceptValue function.
 */
class GetFileInputAcceptValueTest extends TestBaseClass
{
    /**
     * The extensions get a leading dot, are lowercased and trimmed, empty and duplicate entries are dropped.
     */
    public function testGetFileInputAcceptValue()
    {
        \Yii::import('application.helpers.common_helper', true);
        $this->assertSame('.gif,.ico,.jpg,.jpeg,.png', getFileInputAcceptValue('gif,ico,jpg,jpeg,png'));
        $this->assertSame('.css,.png,.svg', getFileInputAcceptValue(' css , PNG,,svg,png,'));
        $this->assertSame('', getFileInputAcceptValue(''));
    }
}
