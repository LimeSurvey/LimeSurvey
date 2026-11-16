<?php

namespace ls\tests;

use Template;

/**
 * Tests for Template::findNameConflict(): Theme names must be unique regardless of case.
 */
class TemplateFindNameConflictTest extends TestBaseClass
{
    /**
     * A name that differs from an existing theme only by case conflicts with it.
     */
    public function testNameDifferingOnlyByCaseConflicts()
    {
        $this->assertSame('vanilla', Template::findNameConflict('VaNiLLa'));
        $this->assertSame('vanilla', Template::findNameConflict('vanilla'));
    }

    /**
     * An unused name does not conflict.
     */
    public function testUnusedNameDoesNotConflict()
    {
        $this->assertNull(Template::findNameConflict('vanilla_' . uniqid()));
    }

    /**
     * The ignored name (e.g. the theme being renamed) does not count as a conflict.
     */
    public function testIgnoredNameDoesNotConflict()
    {
        $this->assertNull(Template::findNameConflict('VANILLA', 'vanilla'));
    }
}
