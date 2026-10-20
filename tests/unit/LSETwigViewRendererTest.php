<?php

namespace ls\tests;

/**
 * @group twig
 */
class LSETwigViewRendererTest extends TestBaseClass
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss');
    }

    public function testResolveI18nQuestionAttributesForLanguage()
    {
        $renderer = \Yii::app()->twigRenderer;

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

        $resolved = $this->getAccessibleMethod('resolveI18nQuestionAttributesForLanguage')->invoke($renderer, $input, 'en');

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
        $resolved = $this->getAccessibleMethod('resolveQuestionL10nLanguage')->invoke(
            \Yii::app()->twigRenderer,
            ['en' => new \stdClass()],
            'de',
            'en',
            123
        );

        $this->assertSame('en', $resolved);
    }

    public function testResolveQuestionL10nLanguageThrowsWhenNoTranslationExists()
    {
        try {
            $this->getAccessibleMethod('resolveQuestionL10nLanguage')->invoke(
                \Yii::app()->twigRenderer,
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

    public function testGetQuestionTemplateDataResolvesCurrentLanguage()
    {
        $question = $this->getTestQuestion();
        $originalLanguage = \Yii::app()->language;
        \Yii::app()->setLanguage('es');

        try {
            $data = $this->getAccessibleMethod('getQuestionTemplateData')->invoke(\Yii::app()->twigRenderer, $question);
        } finally {
            \Yii::app()->setLanguage($originalLanguage);
        }

        $this->assertSame('es', $data['sCurrentLanguage']);
        $this->assertSame($question->qid, $data['questionData']['qid']);
        $this->assertSame('Texto de prueba', $data['questionAttributes']['em_validation_q_tip']);
        $this->assertSame(['es' => 'Texto de prueba'], $data['questionAttributesI18n']['em_validation_q_tip']);
        $this->assertSame('test-class', $data['questionAttributes']['cssclass']);
        $this->assertSame($question->questionl10ns['es']->question, $data['question_text']);
        $this->assertSame($question->questionl10ns['es']->help, $data['question_help']);
    }

    public function testGetQuestionTemplateDataIsCachedPerQuestionAndLanguage()
    {
        $question = $this->getTestQuestion();
        $method = $this->getAccessibleMethod('getQuestionTemplateData');
        // The renderer is an application singleton: drop what earlier tests cached.
        (new \ReflectionProperty(\Yii::app()->twigRenderer, 'questionTemplateDataCache'))->setValue(\Yii::app()->twigRenderer, []);
        $originalLanguage = \Yii::app()->language;
        \Yii::app()->setLanguage('en');

        try {
            $firstData = $method->invoke(\Yii::app()->twigRenderer, $question);

            // Change the attribute in the database: a cached result must not pick up the change.
            \QuestionAttribute::model()->updateAll(
                ['value' => 'changed-class'],
                'qid = :qid AND attribute = :attribute',
                [':qid' => $question->qid, ':attribute' => 'cssclass']
            );
            $secondData = $method->invoke(\Yii::app()->twigRenderer, $this->getTestQuestion());

            \Yii::app()->setLanguage('es');
            $otherLanguageData = $method->invoke(\Yii::app()->twigRenderer, $this->getTestQuestion());
        } finally {
            \Yii::app()->setLanguage($originalLanguage);
            \QuestionAttribute::model()->updateAll(
                ['value' => 'test-class'],
                'qid = :qid AND attribute = :attribute',
                [':qid' => $question->qid, ':attribute' => 'cssclass']
            );
        }

        $this->assertSame($firstData, $secondData);
        $this->assertSame('test-class', $secondData['questionAttributes']['cssclass']);
        $this->assertSame('es', $otherLanguageData['sCurrentLanguage']);
        $this->assertSame('changed-class', $otherLanguageData['questionAttributes']['cssclass']);
    }

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionIsNull()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = null;

        $this->assertRenderQuestionThrowsWithQuestionTemplate($questionTemplate, 'received: NULL');
    }

    public function testRenderQuestionThrowsClearExceptionWhenQuestionTemplateQuestionHasWrongType()
    {
        $questionTemplate = new \QuestionTemplate();
        $questionTemplate->oQuestion = new \stdClass();

        $this->assertRenderQuestionThrowsWithQuestionTemplate($questionTemplate, 'received: ' . \stdClass::class);
    }

    /**
     * Returns a private method of the twig renderer, made accessible.
     *
     * @param string $methodName
     * @return \ReflectionMethod
     */
    private function getAccessibleMethod($methodName)
    {
        $method = new \ReflectionMethod(\Yii::app()->twigRenderer, $methodName);
        return $method;
    }

    /**
     * Returns a freshly loaded instance of the only question of the test survey.
     *
     * @return \Question
     */
    private function getTestQuestion()
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $this->assertInstanceOf(\Question::class, $question);
        return $question;
    }

    /**
     * Renders a question view with the given question template as current instance and asserts that
     * renderQuestion() fails with a clear message. The original instance is restored afterwards.
     *
     * @param \QuestionTemplate $questionTemplate
     * @param string $expectedMessagePart
     * @return void
     */
    private function assertRenderQuestionThrowsWithQuestionTemplate(\QuestionTemplate $questionTemplate, $expectedMessagePart)
    {
        $instanceProperty = new \ReflectionProperty(\QuestionTemplate::class, 'instance');
        $originalInstance = $instanceProperty->getValue();
        $instanceProperty->setValue(null, $questionTemplate);

        try {
            \Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/longfreetext/answer', []);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('QuestionTemplate has no valid Question model', $e->getMessage());
            $this->assertStringContainsString($expectedMessagePart, $e->getMessage());
            $this->assertStringContainsString('view: /survey/questions/answer/longfreetext/answer', $e->getMessage());
        } finally {
            $instanceProperty->setValue(null, $originalInstance);
        }
    }
}
