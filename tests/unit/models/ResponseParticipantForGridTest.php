<?php

namespace ls\tests;

/**
 * Tests for the participant columns (first name, last name, email) of the responses list
 * when the participant data is encrypted.
 */
class ResponseParticipantForGridTest extends TestBaseClass
{
    /** @var integer ID of the response of the participant */
    private static $responseId;

    /**
     * Import an active survey whose participants have an encrypted email address,
     * and add a response of one of the participants.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // The participant columns are only available to users who may read the participants
        \Yii::app()->session['loginID'] = 1;

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_get_participant_properties.lsa');
        if (!self::$testSurvey->getIsActive()) {
            $activator = new \SurveyActivator(self::$testSurvey);
            $activator->activate();
        }
        // The relation to the participants depends on the participant table and the permission of the user
        \Response::resetMetaData(self::$surveyId);
        foreach (\Response::model(self::$surveyId)->findAll() as $response) {
            $response->delete();
        }

        $participant = null;
        foreach (\Token::model(self::$surveyId)->findAll() as $token) {
            if ($token->decrypt()->email === 'p1@mail.com') {
                $participant = $token;
            }
        }
        \Token::model(self::$surveyId)->updateByPk($participant->tid, ['token' => 'gridtest1']);
        self::$responseId = \Response::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'token' => 'gridtest1',
        ]);
    }

    /**
     * Returns the response of the participant, as the responses list loads it
     *
     * @return \Response
     */
    private function getResponse()
    {
        return \Response::model(self::$surveyId)->findByPk(self::$responseId);
    }

    /**
     * The email address is shown decrypted when the first name column is hidden.
     *
     * @return void
     */
    public function testEmailIsDecryptedWithoutFirstName()
    {
        $this->assertSame('p1@mail.com', $this->getResponse()->getEmailForGrid());
    }

    /**
     * All participant columns are shown decrypted in any order, and the participant is not decrypted twice.
     *
     * @return void
     */
    public function testParticipantColumnsAreDecryptedInAnyOrder()
    {
        $response = $this->getResponse();
        $email = $response->getEmailForGrid();
        $firstName = $response->getFirstNameForGrid();
        $lastName = $response->getLastNameForGrid();

        $this->assertSame('p1@mail.com', $email);
        $this->assertSame('p1@mail.com', $response->getEmailForGrid(), 'The email address is still decrypted on the next call.');
        $participant = \Token::model(self::$surveyId)->findByToken('gridtest1')->decrypt();
        $this->assertSame($participant->firstname, $firstName);
        $this->assertSame($participant->lastname, $lastName);
    }
}
