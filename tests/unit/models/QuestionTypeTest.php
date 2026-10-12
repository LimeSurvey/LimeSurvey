<?php

namespace ls\tests;

/**
 * Tests for the static QuestionType model.
 */
class QuestionTypeTest extends TestBaseClass
{
    /**
     * QuestionType::findOne() sets all attributes of the type without logging "unsafe attribute" warnings.
     */
    public function testFindOneSetsAttributesWithoutWarnings()
    {
        $logger = \Yii::getLogger();
        $warningsBefore = count($logger->getLogs('warning'));

        $questionType = \QuestionType::findOne(\QuestionType::QT_L_LIST);

        $this->assertInstanceOf(\QuestionType::class, $questionType);
        $this->assertSame(\QuestionType::QT_L_LIST, $questionType->code);
        $this->assertSame(1, $questionType->answerscales);
        $this->assertSame('list-radio', $questionType->class);
        $this->assertSame([], array_slice($logger->getLogs('warning'), $warningsBefore));
    }

    /**
     * QuestionType::findOne() returns null for an unknown type.
     */
    public function testFindOneUnknownType()
    {
        $this->assertNull(\QuestionType::findOne('unknown'));
    }
}
