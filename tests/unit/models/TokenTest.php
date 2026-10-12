<?php

namespace ls\tests;

use Token;

class TokenTest extends TestBaseClass
{
    /**
     * Import a survey and create its participant table.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        Token::createTable(self::$surveyId);
    }

    /**
     * Attribute values are stored as plain text: Tags are removed, special characters are not encoded.
     *
     * @return void
     */
    public function testSanitizeAttributeKeepsSpecialCharacters()
    {
        $this->assertSame('Müller & Söhne GmbH', Token::sanitizeAttribute('Müller & Söhne <b>GmbH</b>'));
        $this->assertSame('a < b > c "d" \'e\'', Token::sanitizeAttribute('a < b > c "d" \'e\''));
        $this->assertSame('', Token::sanitizeAttribute(null));
    }

    /**
     * Saving a participant again must not add a level of HTML encoding to its name.
     *
     * @return void
     */
    public function testNameIsNotEncodedWhenSavedAgain()
    {
        $token = Token::create(self::$surveyId);
        $token->firstname = 'Müller & Söhne';
        $token->lastname = 'Smith & <b>Sons</b>';
        $this->assertTrue($token->save(), 'Saved participant');

        for ($i = 0; $i < 2; $i++) {
            $token = Token::model(self::$surveyId)->findByPk($token->tid);
            $token->decrypt();
            $this->assertSame('Müller & Söhne', $token->firstname);
            $this->assertSame('Smith & Sons', $token->lastname);
            $this->assertTrue($token->save(), 'Saved participant again');
        }
    }
}
