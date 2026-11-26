<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\QuestionColumnMap;
use LimeSurvey\Models\Services\ResponseFilters\QuestionResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use PHPUnit\Framework\TestCase;

/**
 * Response filters on a survey using the hardened encryption method (#19645):
 * a hardened value never encrypts the same way (random nonce), so only empty / not empty
 * can be filtered on an encrypted question, and encrypted participant attributes can not be filtered.
 */
class HardenedEncryptionFilterTest extends TestCase
{
    private function questionMap(): QuestionColumnMap
    {
        $column = function (array $values): array {
            return $values + ['aid' => null, 'sqid' => null, 'scaleid' => null];
        };
        return QuestionColumnMap::fromQuestionFieldMap([
            '111X1X30' => $column(['qid' => 30, 'type' => 'S']),
            '111X1X85' => $column(['qid' => 85, 'type' => 'S', 'encrypted' => 'Y']),
            '111X1X86' => $column(['qid' => 86, 'type' => 'L', 'encrypted' => 'Y']),
            'Q56_S561' => $column(['qid' => 56, 'type' => 'M', 'aid' => 'SQ001', 'sqid' => 561, 'encrypted' => 'Y']),
            'Q56_Cother' => $column(['qid' => 56, 'type' => 'M', 'aid' => 'other', 'encrypted' => 'Y']),
        ]);
    }

    private function questionResolver(string $encryptionMethod): QuestionResolver
    {
        return new class ($this->questionMap(), $encryptionMethod) extends QuestionResolver {
            protected function encryptValue(string $value): string
            {
                return "enc($value)";
            }
        };
    }

    private function resolveQuestion(string $encryptionMethod, array $entry): array
    {
        $set = ResponseFilterSet::fromRequestValue([['source' => 'question'] + $entry]);
        return $this->questionResolver($encryptionMethod)->resolve($set->all()[0])->getConditions();
    }

    private function participantResolver(string $encryptionMethod): ParticipantResolver
    {
        return new class (['email', 'attribute_1'], false, ['email' => ParticipantResolver::KIND_ENCRYPTED], $encryptionMethod) extends ParticipantResolver {
            protected function encryptValue(string $value): string
            {
                return "enc($value)";
            }
        };
    }

    private function resolveParticipant(string $encryptionMethod, array $entry): array
    {
        $set = ResponseFilterSet::fromRequestValue([['source' => 'participant'] + $entry]);
        return $this->participantResolver($encryptionMethod)->resolve($set->all()[0])->getConditions();
    }

    /** Basic : the value is compared encrypted, as before. */
    public function testBasicEncryptedTextQuestionIsMatchedExactly(): void
    {
        $conditions = $this->resolveQuestion('B', ['qid' => 85, 'text' => 'Sara']);

        $this->assertCount(1, $conditions);
        $this->assertSame(ResolvedCondition::OPERATOR_EQUAL, $conditions[0]->getOperator());
        $this->assertSame('enc(Sara)', $conditions[0]->getValue());
    }

    /** Hardened : a value of an encrypted question can not be matched. */
    public function testHardenedEncryptedTextQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 85 is encrypted with the hardened method, so it can only be filtered as empty or not empty.');
        $this->resolveQuestion('H', ['qid' => 85, 'text' => 'Sara']);
    }

    /** Hardened : the codes of an encrypted choice question can not be matched either. */
    public function testHardenedEncryptedChoiceQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 86 is encrypted with the hardened method');
        $this->resolveQuestion('H', ['qid' => 86, 'answerCodes' => ['A1']]);
    }

    /** Hardened : "Other" of an encrypted multiple choice is a not empty condition, it is allowed. */
    public function testHardenedEncryptedOtherIsANotEmptyCondition(): void
    {
        $conditions = $this->resolveQuestion('H', ['qid' => 56, 'answerCodes' => ['-oth-']]);

        $this->assertCount(1, $conditions);
        $this->assertSame(['Q56_Cother'], $conditions[0]->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_NOT_EMPTY, $conditions[0]->getOperator());
    }

    /** Hardened : questions that are not encrypted are filtered as usual. */
    public function testHardenedDoesNotChangeUnencryptedQuestions(): void
    {
        $conditions = $this->resolveQuestion('H', ['qid' => 30, 'text' => 'Sara']);

        $this->assertCount(1, $conditions);
        $this->assertSame(['111X1X30'], $conditions[0]->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $conditions[0]->getOperator());
    }

    /** Basic : an encrypted participant attribute is compared encrypted, as before. */
    public function testBasicEncryptedParticipantAttributeIsMatchedExactly(): void
    {
        $conditions = $this->resolveParticipant('B', ['attribute' => '{TOKEN:EMAIL}', 'value' => 'sara@example.org']);

        $this->assertCount(1, $conditions);
        $this->assertSame(ResolvedCondition::OPERATOR_EQUAL, $conditions[0]->getOperator());
        $this->assertSame('enc(sara@example.org)', $conditions[0]->getValue());
    }

    /** Hardened : an encrypted participant attribute can not be filtered. */
    public function testHardenedEncryptedParticipantAttributeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Participant attribute 'email' is encrypted with the hardened method, so it cannot be filtered.");
        $this->resolveParticipant('H', ['attribute' => '{TOKEN:EMAIL}', 'value' => 'sara@example.org']);
    }

    /** Hardened : participant attributes that are not encrypted are filtered as usual. */
    public function testHardenedDoesNotChangeUnencryptedParticipantAttributes(): void
    {
        $conditions = $this->resolveParticipant('H', ['attribute' => '{TOKEN:ATTRIBUTE_1}', 'value' => 'blue']);

        $this->assertCount(1, $conditions);
        $this->assertSame(['attribute_1'], $conditions[0]->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $conditions[0]->getOperator());
    }

    /**
     * The filter values are always encrypted with the Basic method (fixed nonce), the only one that
     * can be compared, whatever the method last set on the shared sodium component.
     */
    public function testFilterValuesAreEncryptedWithTheBasicMethod(): void
    {
        $resolver = new class ($this->questionMap(), 'B') extends QuestionResolver {
            public function encryptForTest(string $value): string
            {
                return $this->encryptValue($value);
            }
        };
        \Yii::app()->sodium->setEncryptionMethod('H');

        $encrypted = $resolver->encryptForTest('Sara');

        $this->assertSame(\LSActiveRecord::encryptSingle('Sara', 'B'), $encrypted, 'The filter value must use the Basic method.');
        $this->assertSame($encrypted, $resolver->encryptForTest('Sara'), 'The Basic method gives the same ciphertext every time.');
    }
}
