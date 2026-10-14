<?php

namespace ls\tests;

class QuestionBaseRendererRelevanceClassTest extends TestBaseClass
{
    private const SID = 999001;
    private const SGQA = 'Q10';

    /** @var array|null */
    private $sessionBackup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionBackup = $_SESSION['responses_' . self::SID] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->sessionBackup === null) {
            unset($_SESSION['responses_' . self::SID]);
        } else {
            $_SESSION['responses_' . self::SID] = $this->sessionBackup;
        }
        parent::tearDown();
    }

    /**
     * Build a renderer without running the DB-dependent constructor.
     * Subquestions: A (qid 11), B (qid 12), EX (qid 13, exclusive).
     */
    private function makeRenderer(array $attributes): \QuestionBaseRenderer
    {
        $renderer = (new \ReflectionClass(RelevanceClassTestRenderer::class))->newInstanceWithoutConstructor();
        $renderer->oQuestion = (object) ['sid' => self::SID];
        $renderer->sSGQA = self::SGQA;

        $setProperty = function (string $name, $value) use ($renderer) {
            $property = new \ReflectionProperty(\QuestionBaseRenderer::class, $name);
            $property->setAccessible(true);
            $property->setValue($renderer, $value);
        };
        $setProperty('aQuestionAttributes', $attributes);
        $setProperty('aSubQuestions', [0 => [
            (object) ['title' => 'A', 'qid' => 11],
            (object) ['title' => 'B', 'qid' => 12],
            (object) ['title' => 'EX', 'qid' => 13],
        ]]);

        return $renderer;
    }

    private function setSession(string $exclusiveValue): void
    {
        $_SESSION['responses_' . self::SID] = [
            'relevanceStatus' => [
                self::SGQA . '_S11' => false,
                self::SGQA . '_S12' => false,
            ],
            self::SGQA . '_S13' => $exclusiveValue,
        ];
    }

    public function testRelevantRowHasNoClass()
    {
        $this->setSession('Y');
        $renderer = $this->makeRenderer(['exclude_all_others' => 'EX']);

        $this->assertSame('', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S13'));
    }

    public function testRowsAreDisabledWhenExclusiveOptionIsChecked()
    {
        $this->setSession('Y');
        $renderer = $this->makeRenderer(['exclude_all_others' => 'EX']);

        $this->assertSame('ls-irrelevant ls-disabled', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S11'));
        $this->assertSame('ls-irrelevant ls-disabled', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S12'));
    }

    public function testExclusiveCodeAmongMultipleCodesIsResolved()
    {
        $this->setSession('Y');
        $renderer = $this->makeRenderer(['exclude_all_others' => 'UNKNOWN; EX']);

        $this->assertSame('ls-irrelevant ls-disabled', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S11'));
    }

    public function testRowsAreHiddenWhenExclusiveOptionIsNotChecked()
    {
        $this->setSession('');
        $renderer = $this->makeRenderer(['exclude_all_others' => 'EX']);

        $this->assertSame('ls-irrelevant ls-hidden', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S11'));
    }

    public function testRowsAreDisabledByArrayFilterStyleWithoutExclusiveOption()
    {
        $this->setSession('');
        $renderer = $this->makeRenderer(['array_filter_style' => '1']);

        $this->assertSame('ls-irrelevant ls-disabled', $renderer->getCurrentRelevecanceClass(self::SGQA . '_S11'));
    }
}

class RelevanceClassTestRenderer extends \QuestionBaseRenderer
{
    public function getMainView()
    {
        return '';
    }

    public function getRows()
    {
        return [];
    }

    public function render()
    {
        return '';
    }
}
