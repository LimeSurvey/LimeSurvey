<?php

namespace ls\tests\unit\helpers;

use LimeExpressionManager;
use ls\tests\TestBaseClass;

/**
 * Tests the expansion of the reserved "self" and "that" variables into lists of variable names.
 *
 * @group em
 */
class LimeExpressionManagerSelfThatTest extends TestBaseClass
{
    /** @var array The known variables of the Expression Manager before the test */
    private $originalKnownVars;

    /**
     * Replaces the known variables with an "Array (Texts)" question with a long question ID.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $property = new \ReflectionProperty(LimeExpressionManager::class, 'knownVars');
        $this->originalKnownVars = $property->getValue(LimeExpressionManager::singleton());
        $knownVars = [];
        // The question ID is longer than the question code, so the subquestion codes start before strlen('Q' . qid)
        foreach (['math' => 11, 'biol' => 12] as $rowCode => $rowId) {
            foreach (['G1M1' => 21, 'G1M2' => 22] as $columnCode => $columnId) {
                $fieldName = 'Q1712211_S' . $rowId . '_S' . $columnId;
                $knownVars[$fieldName] = [
                    'qid' => 1712211,
                    'qseq' => 0,
                    'qcode' => 'G1cont_' . $rowCode . '_' . $columnCode,
                    'sgqa' => $fieldName,
                    'rootVarName' => 'G1cont',
                ];
            }
        }
        $knownVars['Q42'] = [
            'qid' => 42,
            'qseq' => 1,
            'qcode' => 'other',
            'sgqa' => 'Q42',
            'rootVarName' => 'other',
        ];
        $property->setValue(LimeExpressionManager::singleton(), $knownVars);
    }

    /**
     * Restores the known variables of the Expression Manager.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $property = new \ReflectionProperty(LimeExpressionManager::class, 'knownVars');
        $property->setValue(LimeExpressionManager::singleton(), $this->originalKnownVars);
        parent::tearDown();
    }

    /**
     * "self" with all subquestions expands to every field of the current question only.
     *
     * @return void
     */
    public function testSelfExpandsToAllFieldsOfTheQuestion(): void
    {
        $this->assertSame(
            'Q1712211_S11_S21.NAOK,Q1712211_S11_S22.NAOK,Q1712211_S12_S21.NAOK,Q1712211_S12_S22.NAOK',
            LimeExpressionManager::GetAllVarNamesForQ(0, 'self.NAOK')
        );
    }

    /**
     * "self.sq_" filters by subquestion code, also when the question ID is longer than the question code.
     *
     * @see https://bugs.limesurvey.org/view.php?id=20887
     * @return void
     */
    public function testSelfSubquestionFilterMatchesSubquestionCode(): void
    {
        $this->assertSame(
            'Q1712211_S11_S21.NAOK,Q1712211_S11_S22.NAOK',
            LimeExpressionManager::GetAllVarNamesForQ(0, 'self.sq_math.NAOK')
        );
        $this->assertSame(
            'Q1712211_S11_S22.NAOK,Q1712211_S12_S22.NAOK',
            LimeExpressionManager::GetAllVarNamesForQ(0, 'self.sq_G1M2.NAOK')
        );
    }

    /**
     * "that.qcode.nosq_" excludes by subquestion code and does not match the question code.
     *
     * @return void
     */
    public function testThatExcludingSubquestionFilterMatchesSubquestionCode(): void
    {
        $this->assertSame(
            'Q1712211_S12_S21,Q1712211_S12_S22',
            LimeExpressionManager::GetAllVarNamesForQ(0, 'that.G1cont.nosq_math')
        );
        // "G1cont" is the question code, not a subquestion code
        $this->assertSame(
            'that.G1cont.sq_cont',
            LimeExpressionManager::GetAllVarNamesForQ(0, 'that.G1cont.sq_cont')
        );
    }
}
