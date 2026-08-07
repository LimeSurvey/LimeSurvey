<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginManager;

class PluginManagerTest extends TestBaseClass
{
    public function testValidatePluginNameAllowsFlatClassNames()
    {
        $pluginManager = new PluginManager();

        $this->assertTrue($pluginManager->validatePluginName('Authdb'));
        $this->assertTrue($pluginManager->validatePluginName('Foo_Bar'));
        $this->assertTrue($pluginManager->validatePluginName('Plugin123'));
    }

    public function testValidatePluginNameRejectsUnsafeOrUnsupportedNames()
    {
        $pluginManager = new PluginManager();

        $cases = [
            '',
            '1Plugin',
            'Foo-Bar',
            'Foo.Bar',
            'Vendor\\Plugin',
            'foo/bar',
            '../twig/extensions/PoC',
        ];

        foreach ($cases as $case) {
            $this->assertFalse(
                $pluginManager->validatePluginName($case),
                'Expected invalid plugin name to be rejected: ' . json_encode($case)
            );
        }
    }
}
