<?php

namespace ls\tests;


class QuestionAttributeTest extends BaseModelTestCase
{
    protected $modelClassName = \QuestionAttribute::class;

    /**
     * @return array<string, array{string, bool}>
     */
    public static function dateLimitProvider()
    {
        return [
            'empty' => ['', true],
            'year' => ['2026', true],
            'date' => ['2026-09-30', true],
            'date with time' => ['2026-09-30 14:30', true],
            'date with seconds' => ['2026-09-30 14:30:15', true],
            'date with T separator' => ['2026-09-30T14:30', true],
            'english description' => ['today', true],
            'relative english description' => ['+6 days', true],
            'expression' => ['Q1', true],
            'expression with quotes' => ['if(Q1 == "Y", "2020-01-01", "2021-01-01")', true],
            'invalid time' => ['2026-09-30 25:00', false],
            'trailing text' => ['2026-09-30 junk', false],
            'js string breakout' => ["2026-09-30';alert(1)//", false],
            'em string breakout' => ["2026-09-30' + \"x\" + '", false],
            'trailing newline' => ["2026-09-30\n;alert(1)", false],
            'expression with comparison' => ['if(Q1<Q2, "2020-01-01", "2021-01-01")', true],
        ];
    }

    /**
     * @dataProvider dateLimitProvider
     * @param string $value
     * @param bool $valid
     */
    public function testValidateDateLimit($value, $valid)
    {
        foreach (['date_min', 'date_max'] as $attributeName) {
            $questionAttribute = new \QuestionAttribute();
            $questionAttribute->attribute = $attributeName;
            $questionAttribute->value = $value;
            $questionAttribute->validateDateLimit('value', []);
            $this->assertSame(!$valid, $questionAttribute->hasErrors('value'), "$attributeName = " . json_encode($value));
        }
    }

    public function testValidateDateLimitIgnoresOtherAttributes()
    {
        $questionAttribute = new \QuestionAttribute();
        $questionAttribute->attribute = 'em_validation_q';
        $questionAttribute->value = "2026-09-30';alert(1)//";
        $questionAttribute->validateDateLimit('value', []);
        $this->assertFalse($questionAttribute->hasErrors('value'));
    }

    /**
     * The newQuestionAttributes event is dispatched once, not for every question type or question,
     * and again when the plugins subscribed to it change.
     */
    public function testPluginAttributesEventIsDispatchedOnce()
    {
        require_once __DIR__ . '/../../data/plugins/NewQuestionAttributesPlugin.php';
        $pluginManager = App()->getPluginManager();
        $plugin = new \NewQuestionAttributesPlugin($pluginManager, 0);
        $plugin->init();
        try {
            $provider = new \LimeSurvey\Models\Services\PluginQuestionAttributeProvider();
            $this->assertArrayHasKey('testAttribute', $provider->getDefinitions(['questionType' => 'S']));
            $this->assertArrayHasKey('testAttributeForArray', $provider->getDefinitions(['questionType' => 'F']));
            $this->assertArrayHasKey('testAttribute', \QuestionAttribute::getOwnQuestionAttributesViaPlugin());
            $this->assertSame(1, $plugin->dispatchCount);
        } finally {
            $pluginManager->unsubscribe($plugin, 'newQuestionAttributes');
        }
        $this->assertArrayNotHasKey('testAttribute', \QuestionAttribute::getOwnQuestionAttributesViaPlugin());
        $this->assertSame(1, $plugin->dispatchCount);
    }
}
