<?php

namespace ls\tests;

use QuestionGroup;

class QuestionGroupTest extends TestBaseClass
{
    /**
     * Setup before class.
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_594264_getGroupDescription.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Test if the group id is invalid or
     * if the group does not exist.
     */
    public function testInvalidGroupId(): void
    {

        $questionGroup = QuestionGroup::model()->findAllByAttributes(array('sid' => self::$surveyId))[0];

        // Invalid group id.
        $this->expectException(\Error::class);
        $enDescription1 = $questionGroup->getGroupDescription(0, 'en');

        // Group does not exist.
        $this->expectException(\Error::class);
        $enDescription1 = $questionGroup->getGroupDescription(3790, 'en');
    }

    /**
     * Test if the language we are passing was not set
     * in the survey. Only English, Spanish and French
     * were set.
     */
    public function testLanguageNotSetInSurvey(): void
    {

        $questionGroup = QuestionGroup::model()->findAllByAttributes(array('sid' => self::$surveyId))[0];

        $itDescription = $questionGroup->getGroupDescription($questionGroup->gid, 'it');

        $this->assertEmpty($itDescription, 'No description in Italian was defined, the function should return an empty string.');
    }

    /**
     * Test if the language we are passing was set in
     * the survey but not defined in the group.
     * English, Spanish and French were set, but only
     * descriptions for the first two were defined.
     */
    public function testLanguageSetInSurveyButNotInGroup(): void
    {

        $questionGroup = QuestionGroup::model()->findAllByAttributes(array('sid' => self::$surveyId))[1];

        $frDescription = $questionGroup->getGroupDescription($questionGroup->gid, 'fr');

        // English was set as the language by default.
        $defaultDescription = $questionGroup->getGroupDescription($questionGroup->gid, 'en');

        $this->assertSame($defaultDescription, $frDescription, 'No description in French was defined, the function should return the description in English.');
    }

    /**
     * Test for descriptions defined.
     */
    public function testDescriptionsSet(): void
    {
        $questionGroups = QuestionGroup::model()->findAllByAttributes(
            array('sid' => self::$surveyId),
            array('order' => 'group_order ASC')
        );

        $questionGroupOne = $questionGroups[0];
        $questionGroupTwo = $questionGroups[1];

        $enDescriptionGroupOne = 'This is the description for the first test question group.';
        $esDescriptionGroupOne = 'Esta es la descripción para el primer grupo de preguntas de prueba.';

        $enDescriptionGroupTwo = 'This is the description for the second question group.';
        $esDescriptionGroupTwo = 'Esta es la descripción para el segundo grupo de preguntas de prueba.';

        $enSetDescriptionGroupOne = $questionGroupOne->getGroupDescription($questionGroupOne->gid, 'en');
        $esSetDescriptionGroupOne = $questionGroupOne->getGroupDescription($questionGroupOne->gid, 'es');

        $enSetDescriptionGroupTwo = $questionGroupTwo->getGroupDescription($questionGroupTwo->gid, 'en');
        $esSetDescriptionGroupTwo = $questionGroupTwo->getGroupDescription($questionGroupTwo->gid, 'es');

        $this->assertSame($enDescriptionGroupOne, $enSetDescriptionGroupOne, 'The English description for group one is not correct.');
        $this->assertSame($esDescriptionGroupOne, $esSetDescriptionGroupOne, 'The Spanish description for group one is not correct.');
        $this->assertSame($enDescriptionGroupTwo, $enSetDescriptionGroupTwo, 'The English description for group two is not correct.');
        $this->assertSame($enDescriptionGroupTwo, $enSetDescriptionGroupTwo, 'The Spanish description for group two is not correct.');
    }
}
