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

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionIsNull()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = null;

        $reflection = new \ReflectionClass(\QuestionTemplate::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $questionTemplate);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('QuestionTemplate has no valid Question model');
        $this->expectExceptionMessage('question template id');

        \Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/answer/longfreetext/answer',
            ['bIsThemeEditor' => true]
        );
    }

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionHasWrongType()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = new \stdClass();

        $reflection = new \ReflectionClass(\QuestionTemplate::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $questionTemplate);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('QuestionTemplate has no valid Question model');
        $this->expectExceptionMessage(\stdClass::class);

        \Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/answer/longfreetext/answer',
            ['bIsThemeEditor' => true]
        );
    }
}
