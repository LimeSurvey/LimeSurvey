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
            'missingLanguageReturnsEmptyString' => [
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
                'missingLanguageReturnsEmptyString' => '',
                'emptyI18nMap' => '',
            ],
            $resolved
        );
    }

    public function testResolveQuestionL10nLanguageFallsBackToSurveyLanguage()
    {
        $renderer = \Yii::app()->twigRenderer;

        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('resolveQuestionL10nLanguage');
        $method->setAccessible(true);

        $resolved = $method->invoke(
            $renderer,
            ['en' => new \stdClass()],
            'de',
            'en',
            123
        );

        $this->assertSame('en', $resolved);
    }

    public function testResolveQuestionL10nLanguageThrowsWhenNoTranslationExists()
    {
        $renderer = \Yii::app()->twigRenderer;

        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('resolveQuestionL10nLanguage');
        $method->setAccessible(true);

        try {
            $method->invoke(
                $renderer,
                ['fr' => new \stdClass()],
                'de',
                'en',
                123
            );
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Question has no translation', $e->getMessage());
            $this->assertStringContainsString('question id: 123', $e->getMessage());
        }
    }

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionIsNull()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = null;

        $reflection = new \ReflectionClass(\QuestionTemplate::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $originalInstance = $instanceProperty->getValue();
        $instanceProperty->setValue(null, $questionTemplate);

        try {
            try {
                \Yii::app()->twigRenderer->renderQuestion(
                    '/survey/questions/answer/longfreetext/answer',
                    []
                );
                $this->fail('Expected InvalidArgumentException was not thrown.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('QuestionTemplate has no valid Question model', $e->getMessage());
                $this->assertStringContainsString('question template id', $e->getMessage());
            }
        } finally {
            $instanceProperty->setValue(null, $originalInstance);
        }
    }

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionHasWrongType()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = new \stdClass();

        $reflection = new \ReflectionClass(\QuestionTemplate::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $originalInstance = $instanceProperty->getValue();
        $instanceProperty->setValue(null, $questionTemplate);

        try {
            try {
                \Yii::app()->twigRenderer->renderQuestion(
                    '/survey/questions/answer/longfreetext/answer',
                    []
                );
                $this->fail('Expected InvalidArgumentException was not thrown.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('QuestionTemplate has no valid Question model', $e->getMessage());
                $this->assertStringContainsString(\stdClass::class, $e->getMessage());
            }
        } finally {
            $instanceProperty->setValue(null, $originalInstance);
        }
    }
}
