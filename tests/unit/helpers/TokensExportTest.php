<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for export_helper::tokensExport() with an encrypted email column.
 *
 * @see https://bugs.limesurvey.org/view.php?id=19272
 */
class TokensExportTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        \Yii::app()->loadHelper('export');

        parent::setUpBeforeClass();

        // The email of the participants (p1@mail.com, p2@mail.com) is encrypted
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_get_participant_properties.lsa');
    }

    /**
     * Resets the POST data after each test.
     */
    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

    /**
     * Filtering by a part of an encrypted email address exports only the matching participants.
     */
    public function testFilterByPartOfEncryptedEmail()
    {
        $_POST = ['filteremail' => 'P1@'];

        $output = $this->export();

        $this->assertStringContainsString('"p1@mail.com"', $output);
        $this->assertStringNotContainsString('"p2@mail.com"', $output);
        $this->assertSame(2, (int) \Token::model(self::$surveyId)->count(), 'No participant must be deleted.');
    }

    /**
     * The survey link column holds the participant-specific survey URL in a survey language,
     * and is empty for participants without an access code.
     */
    public function testIncludeSurveyLink()
    {
        $participants = [];
        foreach (\Token::model(self::$surveyId)->findAll() as $participant) {
            $participants[$participant->decrypt()->email] = $participant;
        }
        // A language that is not a survey language falls back to the base language of the survey
        \Token::model(self::$surveyId)->updateByPk($participants['p1@mail.com']->tid, ['token' => 'linktest1', 'language' => 'zz']);
        \Token::model(self::$surveyId)->updateByPk($participants['p2@mail.com']->tid, ['token' => '']);
        $_POST = ['includesurveylink' => '1'];

        $lines = explode("\n", trim($this->export()));

        $this->assertStringEndsWith(',surveylink', $lines[0]);
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $expectedUrl = $survey->getSurveyUrl($survey->language, ['token' => 'linktest1']);
        $p1Lines = preg_grep('/"p1@mail\.com"/', $lines);
        $p2Lines = preg_grep('/"p2@mail\.com"/', $lines);
        $this->assertCount(1, $p1Lines);
        $this->assertCount(1, $p2Lines);
        $this->assertStringEndsWith(',"' . $expectedUrl . '"', reset($p1Lines));
        $this->assertStringEndsWith(',""', reset($p2Lines));
    }

    /**
     * Deleting exported participants deletes only the participants matching an encrypted email filter.
     */
    public function testDeleteFilteredByPartOfEncryptedEmail()
    {
        $_POST = ['filteremail' => 'p2', 'tokendeleteexported' => '1'];

        $output = $this->export();

        $this->assertStringContainsString('"p2@mail.com"', $output);
        $this->assertStringNotContainsString('"p1@mail.com"', $output);
        $remaining = \Token::model(self::$surveyId)->findAll();
        $this->assertCount(1, $remaining);
        $this->assertSame('p1@mail.com', $remaining[0]->decrypt()->email);
    }

    /**
     * Runs the participant export of the test survey.
     *
     * @return string The exported CSV
     */
    private function export(): string
    {
        ob_start();
        tokensExport(self::$surveyId);
        return ob_get_clean();
    }
}
