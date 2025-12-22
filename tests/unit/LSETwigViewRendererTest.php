<?php

namespace ls\tests;

/**
 * @group twig
 */
class LSETwigViewRendererTest extends TestBaseClass
{
    public function testResolveI18nQuestionAttributesForLanguage()
    {
        $renderer = \Yii::app()->twigRenderer;

        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('resolveI18nQuestionAttributesForLanguage');
        $method->setAccessible(true);

        $input = [
            'max_answers' => 2,
            'em_validation_q_tip' => [
                'en' => 'Test string',
                'es' => 'Texto de prueba',
            ],
            'missingLanguageFallsBackToFirst' => [
                'de' => 'Deutsch',
                'fr' => 'Français',
            ],
            'emptyI18nMap' => [],
        ];

        $resolved = $method->invoke($renderer, $input, 'en');

        $this->assertSame(
            [
                'max_answers' => 2,
                'em_validation_q_tip' => 'Test string',
                'missingLanguageFallsBackToFirst' => 'Deutsch',
                'emptyI18nMap' => '',
            ],
            $resolved
        );
    }
}

