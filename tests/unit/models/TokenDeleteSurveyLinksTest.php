<?php

namespace ls\tests;

use Token;

/**
 * Tests for the links to the central participant database when survey participants are deleted.
 */
class TokenDeleteSurveyLinksTest extends TestBaseClass
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
     * Remove the survey links of the test survey.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \SurveyLink::model()->deleteAllByAttributes(['survey_id' => self::$surveyId]);
        parent::tearDownAfterClass();
    }

    /**
     * Add a participant to the survey
     *
     * @param array $attributes
     * @return integer The participant ID
     */
    private function addParticipant(array $attributes)
    {
        $token = Token::create(self::$surveyId);
        $token->setAttributes($attributes, false);
        $this->assertTrue($token->save(), 'Saved participant');
        return (int) $token->tid;
    }

    /**
     * Deleting participants deletes the links to the central participant database of these participants only.
     *
     * @return void
     */
    public function testDeleteAllByAttributesDeletesTheSurveyLinksOfTheDeletedParticipants()
    {
        $tokenIds = [];
        foreach (['linked-1', 'linked-2', 'linked-3'] as $participantId) {
            $tokenIds[$participantId] = $this->addParticipant(['participant_id' => $participantId]);
            \Yii::app()->db->createCommand()->insert('{{survey_links}}', [
                'participant_id' => $participantId,
                'token_id' => $tokenIds[$participantId],
                'survey_id' => self::$surveyId,
                'date_created' => date('Y-m-d H:i:s'),
            ]);
        }
        $notLinkedId = $this->addParticipant(['firstname' => 'Not linked']);

        $deleted = Token::model(self::$surveyId)->deleteAllByAttributes(
            ['tid' => [$tokenIds['linked-1'], $tokenIds['linked-2'], $notLinkedId]]
        );

        $this->assertEquals(3, $deleted);
        $this->assertEquals(1, Token::model(self::$surveyId)->count());
        $remainingLinks = \Yii::app()->db->createCommand()
            ->select('participant_id')
            ->from('{{survey_links}}')
            ->where('survey_id = :sid', [':sid' => self::$surveyId])
            ->queryColumn();
        $this->assertSame(['linked-3'], $remainingLinks, 'Only the link of the remaining participant is kept.');
    }

    /**
     * A condition in addition to the attributes limits the deleted participants and survey links too.
     *
     * @return void
     */
    public function testConditionLimitsTheDeletedSurveyLinks()
    {
        Token::model(self::$surveyId)->deleteAll();
        \SurveyLink::model()->deleteAllByAttributes(['survey_id' => self::$surveyId]);
        $tokenIds = [];
        foreach (['linked-a' => 'en', 'linked-b' => 'de'] as $participantId => $language) {
            $tokenIds[$participantId] = $this->addParticipant(['participant_id' => $participantId, 'language' => $language]);
            \Yii::app()->db->createCommand()->insert('{{survey_links}}', [
                'participant_id' => $participantId,
                'token_id' => $tokenIds[$participantId],
                'survey_id' => self::$surveyId,
                'date_created' => date('Y-m-d H:i:s'),
            ]);
        }

        $deleted = Token::model(self::$surveyId)->deleteAllByAttributes(
            ['tid' => array_values($tokenIds)],
            'language = :language',
            [':language' => 'de']
        );

        $this->assertEquals(1, $deleted);
        $remainingLinks = \Yii::app()->db->createCommand()
            ->select('participant_id')
            ->from('{{survey_links}}')
            ->where('survey_id = :sid', [':sid' => self::$surveyId])
            ->queryColumn();
        $this->assertSame(['linked-a'], $remainingLinks);
    }
}
