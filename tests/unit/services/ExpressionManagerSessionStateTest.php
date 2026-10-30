<?php

namespace ls\tests\unit\services;

use LimeSurvey\Models\Services\ExpressionManagerSessionState;
use PHPUnit\Framework\TestCase;

class ExpressionManagerSessionStateTest extends TestCase
{
    /** @var array|null Session content before the test, restored afterwards */
    private $sessionBackup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionBackup = $_SESSION ?? null;
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->sessionBackup;
        parent::tearDown();
    }

    /**
     * @testdox current() returns one shared instance
     */
    public function testCurrentIsShared()
    {
        $this->assertSame(ExpressionManagerSessionState::current(), ExpressionManagerSessionState::current());
    }

    /**
     * @testdox Survey ID is stored in LEMsid and returned as int
     */
    public function testSurveyId()
    {
        $state = new ExpressionManagerSessionState();
        $this->assertFalse($state->hasSurveyId());
        $this->assertNull($state->getSurveyId());

        $state->setSurveyId(123);
        $this->assertSame(123, $_SESSION['LEMsid']);

        $_SESSION['LEMsid'] = '456';
        $this->assertTrue($state->hasSurveyId());
        $this->assertSame(456, $state->getSurveyId());
    }

    /**
     * @testdox Language is stored in LEMlang
     */
    public function testLanguage()
    {
        $state = new ExpressionManagerSessionState();
        $this->assertFalse($state->hasLanguage());
        $this->assertNull($state->getLanguage());

        $state->setLanguage('de');
        $this->assertTrue($state->hasLanguage());
        $this->assertSame('de', $state->getLanguage());
        $this->assertSame('de', $_SESSION['LEMlang']);
    }

    /**
     * @testdox Serialized instance is stored in LEMsingleton and can be cleared
     */
    public function testSerializedInstance()
    {
        $state = new ExpressionManagerSessionState();
        $this->assertNull($state->getSerializedInstance());

        $state->setSerializedInstance('O:8:"stdClass":0:{}');
        $this->assertSame('O:8:"stdClass":0:{}', $_SESSION['LEMsingleton']);
        $this->assertSame('O:8:"stdClass":0:{}', $state->getSerializedInstance());

        $state->clearSerializedInstance();
        $this->assertArrayNotHasKey('LEMsingleton', $_SESSION);
        $this->assertNull($state->getSerializedInstance());
    }

    /**
     * @testdox Dirty and force refresh flags are set and cleared independently
     */
    public function testFlags()
    {
        $state = new ExpressionManagerSessionState();
        $this->assertFalse($state->isDirty());
        $this->assertFalse($state->isForceRefreshRequested());

        $state->markDirty();
        $state->requestForceRefresh();
        $this->assertTrue($_SESSION['LEMdirtyFlag']);
        $this->assertTrue($_SESSION['LEMforceRefresh']);
        $this->assertTrue($state->isDirty());
        $this->assertTrue($state->isForceRefreshRequested());

        $state->clearDirty();
        $this->assertFalse($state->isDirty());
        $this->assertTrue($state->isForceRefreshRequested());

        $state->clearForceRefresh();
        $this->assertFalse($state->isForceRefreshRequested());
    }

    /**
     * @testdox Does not touch the per-survey state
     */
    public function testKeepsSurveyStateApart()
    {
        $_SESSION['responses_1'] = ['step' => 2];
        $state = new ExpressionManagerSessionState();
        $state->setSurveyId(1);
        $state->setLanguage('en');
        $state->markDirty();
        $state->clearSerializedInstance();

        $this->assertSame(['step' => 2], $_SESSION['responses_1']);
    }
}
