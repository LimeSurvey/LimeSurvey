<?php

namespace ls\tests;

use LS_Twig_Extension;

/**
 * Tests for LS_Twig_Extension::flatString().
 */
class LSTwigExtensionFlatStringTest extends TestBaseClass
{
    /**
     * Entities already present in the text (from the HTML editor or the XSS filter) are not encoded again.
     */
    public function testEncodeKeepsExistingEntities()
    {
        $this->assertSame('Cats &amp; dogs', LS_Twig_Extension::flatString('Cats &amp; dogs', true));
        $this->assertSame('a &lt; b&nbsp;c', LS_Twig_Extension::flatString('a &lt; b&nbsp;c', true));
    }

    /**
     * Plain special chars are still encoded and tags are removed.
     */
    public function testEncodeEncodesSpecialChars()
    {
        $this->assertSame('Cats &amp; dogs', LS_Twig_Extension::flatString('Cats & dogs', true));
        $this->assertSame('&quot;Fish&quot; &amp; &#039;chips&#039;', LS_Twig_Extension::flatString('"Fish" & \'chips\'', true));
        $this->assertSame('bold text', LS_Twig_Extension::flatString('<b>bold</b> text', true));
    }

    /**
     * Without encoding the flattened text is returned as is.
     */
    public function testNoEncode()
    {
        $this->assertSame('Cats & dogs', LS_Twig_Extension::flatString("<p>Cats &\ndogs</p>"));
    }
}
