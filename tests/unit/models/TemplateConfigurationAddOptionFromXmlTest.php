<?php

namespace ls\tests;

class TemplateConfigurationAddOptionFromXmlTest extends TestBaseClass
{
    private $originalDefaultTheme;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDefaultTheme = \Yii::app()->getConfig('defaulttheme');
    }

    protected function tearDown(): void
    {
        \Yii::app()->setConfig('defaulttheme', $this->originalDefaultTheme);
        parent::tearDown();
    }

    /**
     * Creates a fruity_twentythree configuration that records added options instead of saving them.
     */
    private function makeConfig($options)
    {
        $config = new class extends \TemplateConfiguration {
            public $added = [];

            public function addOptionToLiveTheme($name, $value)
            {
                $this->added[$name] = (string) $value;
            }
        };
        $config->template_name = 'fruity_twentythree';
        $config->options = $options;
        return $config;
    }

    public function testInvalidOptionsAreSkipped()
    {
        foreach ([null, '', 'not json', '"string"'] as $options) {
            $config = $this->makeConfig($options);
            $config->addOptionFromXMLToLiveTheme();
            $this->assertSame([], $config->added);
        }
    }

    public function testUsesOwnManifestWhenDefaultThemeDiffers()
    {
        \Yii::app()->setConfig('defaulttheme', 'vanilla');
        $config = $this->makeConfig('{}');
        $config->addOptionFromXMLToLiveTheme();
        $this->assertArrayHasKey('automatichyphenation', $config->added);
        $this->assertSame('on', $config->added['automatichyphenation']);
    }
}
