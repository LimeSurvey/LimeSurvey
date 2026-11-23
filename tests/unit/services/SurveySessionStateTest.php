<?php

namespace ls\tests\unit\services;

use LimeSurvey\Models\Services\SurveySessionState;
use PHPUnit\Framework\TestCase;

class SurveySessionStateTest extends TestCase
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
     * @testdox Reads and writes the responses_<surveyid> session entry
     */
    public function testUsesSurveySessionKey()
    {
        $state = new SurveySessionState(12345);

        $this->assertSame(12345, $state->getSurveyId());
        $this->assertSame('responses_12345', $state->getSessionKey());
        $this->assertFalse($state->exists());

        $state->setStep(3);

        $this->assertTrue($state->exists());
        $this->assertSame(['step' => 3], $_SESSION['responses_12345']);
    }

    /**
     * @testdox Sees changes made directly to $_SESSION, since it keeps no copy
     */
    public function testReadsSessionLive()
    {
        $state = new SurveySessionState(12345);
        $this->assertNull($state->getStep());

        $_SESSION['responses_12345']['step'] = 2;
        $this->assertSame(2, $state->getStep());

        unset($_SESSION['responses_12345']);
        $this->assertFalse($state->exists());
        $this->assertNull($state->getStep());
    }

    /**
     * @testdox Does not touch the state of other surveys
     */
    public function testKeepsSurveysApart()
    {
        $_SESSION['responses_1']['step'] = 5;
        $state = new SurveySessionState(2);

        $state->setStep(1);

        $this->assertSame(5, $_SESSION['responses_1']['step']);
        $this->assertSame(1, $_SESSION['responses_2']['step']);
    }

    /**
     * @testdox has() follows isset() semantics, get() falls back to the default
     */
    public function testGenericAccessors()
    {
        $state = new SurveySessionState(1);
        $_SESSION['responses_1']['nullvalue'] = null;
        $_SESSION['responses_1']['zero'] = 0;

        $this->assertFalse($state->has('missing'));
        $this->assertFalse($state->has('nullvalue'));
        $this->assertTrue($state->has('zero'));
        $this->assertSame('default', $state->get('missing', 'default'));
        $this->assertSame('default', $state->get('nullvalue', 'default'));
        $this->assertSame(0, $state->get('zero', 'default'));

        $state->set('custom', ['a' => 1]);
        $this->assertSame(['a' => 1], $state->get('custom'));

        $state->remove('custom');
        $this->assertFalse($state->has('custom'));
    }

    /**
     * @testdox Step numbers stored as strings (e.g. loaded from the response table) are returned as int
     */
    public function testStepNumbersAreCastToInt()
    {
        $state = new SurveySessionState(1);
        $_SESSION['responses_1'] = [
            'step' => '4',
            'maxstep' => '6',
            'totalsteps' => '8',
            'totalVisibleSteps' => '7',
            'srid' => '42',
        ];

        $this->assertSame(4, $state->getStep());
        $this->assertSame(6, $state->getMaxStep());
        $this->assertSame(8, $state->getTotalSteps());
        $this->assertSame(7, $state->getTotalVisibleSteps());
        $this->assertSame(42, $state->getResponseId());
    }

    /**
     * @testdox Unset step counters are null, except the skipped step counts which default to 0
     */
    public function testUnsetStepNumbers()
    {
        $state = new SurveySessionState(1);

        $this->assertNull($state->getStep());
        $this->assertNull($state->getPrevStep());
        $this->assertNull($state->getMaxStep());
        $this->assertNull($state->getTotalSteps());
        $this->assertNull($state->getResponseId());
        $this->assertSame(0, $state->getNotRelevantSteps());
        $this->assertSame(0, $state->getHiddenSteps());
    }

    /**
     * @testdox The previous step keeps move names as they are
     */
    public function testPrevStepAcceptsMoveNames()
    {
        $state = new SurveySessionState(1);

        $state->setPrevStep(3);
        $this->assertSame(3, $state->getPrevStep());

        $state->setPrevStep('changelang');
        $this->assertSame('changelang', $state->getPrevStep());
    }

    /**
     * @testdox Fill token can be set and removed, and removing a missing one is harmless
     */
    public function testFillToken()
    {
        $state = new SurveySessionState(1);
        $state->removeFillToken();
        $this->assertNull($state->getFillToken());

        $state->setFillToken('abc');
        $this->assertSame('abc', $state->getFillToken());

        $state->removeFillToken();
        $this->assertNull($state->getFillToken());
    }

    /**
     * @testdox addToInsertArray() adds a field only once and creates the list if missing
     */
    public function testAddToInsertArray()
    {
        $state = new SurveySessionState(1);
        $this->assertSame([], $state->getInsertArray());

        $state->addToInsertArray('refurl');
        $state->addToInsertArray('refurl');
        $this->assertSame(['refurl'], $state->getInsertArray());

        $_SESSION['responses_1']['insertarray'] = ['token', 'submitdate'];
        $state->addToInsertArray('refurl');
        $this->assertSame(['token', 'submitdate', 'refurl'], $state->getInsertArray());
    }

    /**
     * @testdox Group list and field array default to empty arrays
     */
    public function testListsDefaultToEmptyArrays()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->hasGroupList());
        $this->assertSame([], $state->getGroupList());
        $this->assertSame([], $state->getFieldArray());

        $groups = [['gid' => 10, 'group_name' => 'G1']];
        $_SESSION['responses_1']['grouplist'] = $groups;
        $this->assertTrue($state->hasGroupList());
        $this->assertSame($groups, $state->getGroupList());
    }

    /**
     * @testdox Detects the randomized field map of its own survey only
     */
    public function testHasRandomizedFieldMap()
    {
        $state = new SurveySessionState(7);
        $_SESSION['responses_7']['fieldmap-8-randMaster'] = 'x';
        $this->assertFalse($state->hasRandomizedFieldMap());

        $_SESSION['responses_7']['fieldmap-7-randMaster'] = 'x';
        $this->assertTrue($state->hasRandomizedFieldMap());
    }

    /**
     * @testdox Flags: token resume, browser navigation warning, captcha, saved control
     */
    public function testFlags()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->isTokenResume());
        $this->assertFalse($state->isBrowserNavigationWarningIgnored());
        $this->assertFalse($state->isCaptchaPassed('surveyaccessscreen'));
        $this->assertFalse($state->hasSavedControl());

        $_SESSION['responses_1']['LEMtokenResume'] = true;
        $_SESSION['responses_1']['scid'] = 5;
        $state->ignoreBrowserNavigationWarning();
        $state->setCaptchaPassed('surveyaccessscreen');

        $this->assertTrue($state->isTokenResume());
        $this->assertTrue($state->isBrowserNavigationWarningIgnored());
        $this->assertTrue($state->isCaptchaPassed('surveyaccessscreen'));
        $this->assertFalse($state->isCaptchaPassed('registrationscreen'));
        $this->assertTrue($state->hasSavedControl());
        $this->assertTrue($_SESSION['responses_1']['captcha_surveyaccessscreen']);

        $state->clearTokenResume();
        $this->assertFalse($state->isTokenResume());
    }

    /**
     * @testdox markFinished() sets the finished flag and the survey ID
     */
    public function testMarkFinished()
    {
        $state = new SurveySessionState(99);
        $this->assertFalse($state->isFinished());

        $state->markFinished();

        $this->assertTrue($state->isFinished());
        $this->assertTrue($_SESSION['responses_99']['finished']);
        $this->assertSame(99, $_SESSION['responses_99']['sid']);
    }

    /**
     * @testdox forSurvey() returns one shared instance per survey
     */
    public function testForSurveySharesInstances()
    {
        $this->assertSame(SurveySessionState::forSurvey(5), SurveySessionState::forSurvey(5));
        $this->assertNotSame(SurveySessionState::forSurvey(5), SurveySessionState::forSurvey(6));
        $this->assertSame(6, SurveySessionState::forSurvey(6)->getSurveyId());
    }

    /**
     * @testdox toArray() returns a snapshot, clear() removes the survey state only
     */
    public function testToArrayAndClear()
    {
        $state = new SurveySessionState(1);
        $this->assertSame([], $state->toArray());

        $_SESSION['responses_1'] = ['step' => 2];
        $_SESSION['responses_2'] = ['step' => 3];
        $this->assertSame(['step' => 2], $state->toArray());

        $state->clear();
        $this->assertFalse($state->exists());
        $this->assertSame(['step' => 3], $_SESSION['responses_2']);
    }

    /**
     * @testdox Answer values are stored by field name next to the state keys
     */
    public function testFieldValues()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->hasFieldValue('1X2X3'));
        $this->assertSame('', $state->getFieldValue('1X2X3', ''));

        $state->setFieldValue('1X2X3', 'A1');
        $this->assertTrue($state->hasFieldValue('1X2X3'));
        $this->assertSame('A1', $_SESSION['responses_1']['1X2X3']);

        $state->setFieldValue('1X2X3', null);
        $this->assertFalse($state->hasFieldValue('1X2X3'));

        $state->setFieldValue('1X2X4', '0');
        $state->removeFieldValue('1X2X4');
        $this->assertArrayNotHasKey('1X2X4', $_SESSION['responses_1']);
    }

    /**
     * @testdox Relevance is read and written per group, question or row
     */
    public function testRelevance()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->hasRelevanceStatus());
        $this->assertSame(1, $state->getRelevance('G0', 1));

        $state->setRelevance('G0', false);
        $state->setRelevance(12, 1);
        $this->assertTrue($state->hasRelevanceStatus());
        $this->assertTrue($state->hasRelevance('G0'));
        $this->assertFalse($state->getRelevance('G0', 1));
        $this->assertSame(1, $state->getRelevance(12));
        $this->assertSame(['G0' => false, 12 => 1], $state->getRelevanceStatus());

        $state->setRelevanceStatus([]);
        $this->assertFalse($state->hasRelevance('G0'));
    }

    /**
     * @testdox Starting values can be read and changed one by one
     */
    public function testStartingValues()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->hasStartingValues());
        $this->assertSame([], $state->getStartingValues());

        $state->setStartingValue('seed', '123');
        $state->setStartingValue('Q1', 'A');
        $this->assertTrue($state->hasStartingValues());
        $this->assertTrue($state->hasStartingValue('Q1'));
        $this->assertSame('123', $state->getStartingValue('seed'));

        $state->removeStartingValue('Q1');
        $this->assertSame(['seed' => '123'], $state->getStartingValues());

        $state->setStartingValues(['Q2' => 'B']);
        $this->assertSame('B', $state->getStartingValue('Q2'));
        $this->assertNull($state->getStartingValue('seed'));
    }

    /**
     * @testdox The randomized field map is stored per language and found through the master key
     */
    public function testRandomizedFieldMap()
    {
        $state = new SurveySessionState(7);
        $this->assertNull($state->getRandomizedFieldMap());

        $fieldMap = ['7X1X1' => ['qid' => 1]];
        $state->setRandomizedFieldMap('de', $fieldMap);

        $this->assertTrue($state->hasRandomizedFieldMap());
        $this->assertSame('fieldmap-7de', $_SESSION['responses_7']['fieldmap-7-randMaster']);
        $this->assertSame($fieldMap, $_SESSION['responses_7']['fieldmap-7de']);
        $this->assertSame($fieldMap, $state->getRandomizedFieldMap());

        $state->clearRandomizedFieldMap();
        $this->assertFalse($state->hasRandomizedFieldMap());
        $this->assertNull($state->getRandomizedFieldMap());
        $this->assertArrayHasKey('fieldmap-7de', $_SESSION['responses_7']);
    }

    /**
     * @testdox Simple setters store the values under the existing session keys
     */
    public function testSettersUseExistingKeys()
    {
        $state = new SurveySessionState(1);
        $state->setFieldMap(['a' => []]);
        $state->setGroupList([['gid' => 1]]);
        $state->setGroupReMap([1 => 2]);
        $state->setFieldArray(['Q1' => [1]]);
        $state->setInsertArray(['token']);
        $state->setFieldNamesInfo(['1X1X1' => 'Q1']);
        $state->setLanguage('de');
        $state->setRefUrl('https://example.org');
        $state->setToken('tok');
        $state->setTokenUsed('tok');
        $state->setTotalSteps(4);
        $state->setTotalVisibleSteps(3);
        $state->setTotalQuestions(9, 8);
        $state->setUrlParam('src', 'mail');
        $state->setDatestamp('2026-01-01 00:00:00');
        $state->setStartDate('2026-01-01 00:00:00');
        $state->setSavedControlId(5);
        $state->setSecurityAnswer(12);
        $state->setRandomized(true);
        $state->setTokenResume();
        $state->setResponseId(42);

        $this->assertSame([
            'fieldmap' => ['a' => []],
            'grouplist' => [['gid' => 1]],
            'groupReMap' => [1 => 2],
            'fieldarray' => ['Q1' => [1]],
            'insertarray' => ['token'],
            'fieldnamesInfo' => ['1X1X1' => 'Q1'],
            's_lang' => 'de',
            'refurl' => 'https://example.org',
            'token' => 'tok',
            'tokenused' => 'tok',
            'totalsteps' => 4,
            'totalVisibleSteps' => 3,
            'totalquestions' => 9,
            'totalVisibleQuestions' => 8,
            'urlparams' => ['src' => 'mail'],
            'datestamp' => '2026-01-01 00:00:00',
            'startdate' => '2026-01-01 00:00:00',
            'scid' => 5,
            'secanswer' => 12,
            'randomized' => true,
            'LEMtokenResume' => true,
            'srid' => 42,
        ], $_SESSION['responses_1']);

        $this->assertTrue($state->hasFieldMap());
        $this->assertTrue($state->hasFieldArray());
        $this->assertSame(9, $state->getTotalQuestions());
        $this->assertSame(['src' => 'mail'], $state->getUrlParams());
        $this->assertSame('tok', $state->getTokenUsed());
        $this->assertSame(5, $state->getSavedControlId());
        $this->assertSame(12, $state->getSecurityAnswer());
        $this->assertTrue($state->isRandomized());
        $this->assertTrue($state->isTokenResume());

        $state->removeGroupList();
        $this->assertFalse($state->hasGroupList());
    }

    /**
     * @testdox Per-question timers use the timer_question_<qid> keys
     */
    public function testTimers()
    {
        $state = new SurveySessionState(1);
        $this->assertFalse($state->hasQuestionTimer(12));
        $this->assertNull($state->getQuestionTimer(12));

        // Written by the ExpressionManager as a field value, as a string or a float
        $state->setFieldValue(SurveySessionState::QUESTION_TIMER_KEY_PREFIX . '12', '25');
        $state->setFieldValue('timer_question_13', 7.5);
        $this->assertTrue($state->hasQuestionTimer(12));
        $this->assertSame(25.0, $state->getQuestionTimer(12));
        $this->assertSame(7.5, $state->getQuestionTimer(13));

        $state->setFieldValue('timer_question_14', 'abc');
        $this->assertTrue($state->hasQuestionTimer(14));
        $this->assertNull($state->getQuestionTimer(14));
    }

    /**
     * @testdox Debug level defaults to 0 and is cast to int
     */
    public function testDebugLevel()
    {
        $state = new SurveySessionState(1);
        $this->assertSame(0, $state->getDebugLevel());

        $state->setDebugLevel(3);
        $this->assertSame(3, $_SESSION['responses_1']['LEMdebugLevel']);

        $_SESSION['responses_1']['LEMdebugLevel'] = '4';
        $this->assertSame(4, $state->getDebugLevel());
    }

    /**
     * @testdox Filtered columns are null until set, an empty choice stays an empty array
     */
    public function testFilteredColumns()
    {
        $state = new SurveySessionState(1);
        $this->assertNull($state->getFilteredColumns());

        $state->setFilteredColumns([]);
        $this->assertSame([], $state->getFilteredColumns());

        $state->setFilteredColumns(['id', 'submitdate']);
        $this->assertSame(['id', 'submitdate'], $_SESSION['responses_1']['filteredColumns']);
        $this->assertSame(['id', 'submitdate'], $state->getFilteredColumns());
    }

    /**
     * @testdox getFinishedSurveyId() returns the ID stored by markFinished()
     */
    public function testFinishedSurveyId()
    {
        $state = new SurveySessionState(99);
        $this->assertNull($state->getFinishedSurveyId());

        $state->markFinished();
        $this->assertSame(99, $state->getFinishedSurveyId());
    }

    /**
     * @testdox Visible questions and the remaining setters and removers use the existing keys
     */
    public function testMoreKeys()
    {
        $state = new SurveySessionState(1);
        $this->assertNull($state->getTotalVisibleQuestions());

        $state->setTotalQuestions(9, 8);
        $this->assertSame(8, $state->getTotalVisibleQuestions());

        $state->setRefUrl('https://example.org');
        $state->setFieldArray(['Q1' => [1]]);
        $state->setInsertArray(['token']);
        $state->setFieldNamesInfo(['1X1X1' => 'Q1']);
        $state->setGroupReMap([1 => 2]);

        $state->removeRefUrl();
        $state->removeFieldArray();
        $state->removeInsertArray();
        $state->removeFieldNamesInfo();
        $state->removeGroupReMap();

        foreach (['refurl', 'fieldarray', 'insertarray', 'fieldnamesInfo', 'groupReMap'] as $key) {
            $this->assertArrayNotHasKey($key, $_SESSION['responses_1']);
        }
        $this->assertNull($state->getRefUrl());
        $this->assertFalse($state->hasFieldArray());
    }
}
