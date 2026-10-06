<?php

namespace ls\tests;

use LimeSurvey\Models\Services\ReactQuestionAttributeConfig;

class ReactQuestionAttributeConfigTest extends TestBaseClass
{
    /** @var ReactQuestionAttributeConfig */
    private static $config;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$config = new ReactQuestionAttributeConfig();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getListRadioAttributes(): array
    {
        $file = \Yii::app()->getConfig('rootdir') . '/application/views/survey/questions/answer/listradio/config.xml';
        $config = \ExtensionConfig::loadFromFile($file);

        $attributes = [];
        foreach ($config->getNodeAsArray('generalattributes')['attribute'] as $name) {
            $attributes[$name] = ['name' => $name, 'general' => true];
        }
        foreach ($config->getNodeAsArray('attributes')['attribute'] as $attribute) {
            $attributes[$attribute['name']] = $attribute;
        }
        return $attributes;
    }

    public function testVirtualTimerToggleIsAddedWhenItsDependentsExist(): void
    {
        $result = self::$config->apply($this->getListRadioAttributes(), 'listradio');

        $this->assertArrayHasKey('use_first_limit_warning', $result);
        $this->assertSame('Timer', $result['use_first_limit_warning']['tab']);
        $this->assertSame('attributes.use_first_limit_warning', $result['use_first_limit_warning']['attributePath']);
        $this->assertSame(
            [['attribute' => 'use_first_limit_warning']],
            $result['time_limit_warning']['dependsOn']
        );
        $this->assertSame(['onFalse' => ''], $result['time_limit_warning']['onDependsToggle']);
    }

    public function testVirtualTimerToggleIsNotAddedWithoutTimerAttributes(): void
    {
        $result = self::$config->apply(['mandatory' => ['name' => 'mandatory', 'general' => true]], 'listradio');

        $this->assertArrayNotHasKey('use_first_limit_warning', $result);
        $this->assertArrayHasKey('title', $result, 'Attributes with always=1 are always added');
    }

    public function testOverlayIsMergedFlatAndConfigCategoryWins(): void
    {
        $result = self::$config->apply($this->getListRadioAttributes(), 'listradio');

        $this->assertSame('Select', $result['answer_order']['component']);
        $this->assertSame('order', $result['answer_order']['optionsPreset']);
        $this->assertSame('Display', $result['answer_order']['tab']);
        $this->assertSame('normal', $result['answer_order']['options'][0]['value']);
        $this->assertSame('mandatory', $result['mandatory']['attributePath']);
        $this->assertTrue($result['time_limit_message']['languageBased']);
    }

    public function testMultipleConditionsAreReturnedAsList(): void
    {
        $result = self::$config->apply($this->getListRadioAttributes(), 'listradio');

        $this->assertSame(
            [['attribute' => 'other'], ['attribute' => 'other_position', 'value' => 'specific']],
            $result['other_position_code']['dependsOn']
        );
    }

    public function testSimpleOrderUsesThemeListOrDefault(): void
    {
        $attributes = $this->getListRadioAttributes();
        $attributes['numbers_only'] = ['name' => 'numbers_only', 'category' => 'Input'];

        $default = self::$config->apply($attributes, 'listradio');
        $this->assertSame(0, $default['title']['simpleOrder']);
        $this->assertSame(5, $default['image']['simpleOrder']);
        $this->assertArrayNotHasKey('simpleOrder', $default['numbers_only']);

        $shortText = self::$config->apply($attributes, 'shortfreetext');
        $this->assertSame(3, $shortText['numbers_only']['simpleOrder']);
    }

    public function testQuestionHelperOptionFormatIsNormalized(): void
    {
        $result = self::$config->apply(
            ['custom' => ['name' => 'custom', 'category' => 'Display', 'options' => ['a' => 'A', 'b' => 'B']]],
            'listradio'
        );

        $this->assertSame([['value' => 'a', 'text' => 'A'], ['value' => 'b', 'text' => 'B']], $result['custom']['options']);
    }
}
