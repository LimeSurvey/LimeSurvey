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

        $questionGroup = new QuestionGroup();

        // Invalid group id.
        $this->expectException(\Error::class);
        $esDescription1 = $questionGroup->getGroupDescription(0, 'en');

        // Group does not exist.
        $this->expectException(\Error::class);
        $esDescription1 = $questionGroup->getGroupDescription(3790, 'en');
    }

    /**
     * Test if the language we are passing was not set
     * in the survey. Only English, Spanish and French
     * were set.
     */
    public function testLanguageNotSetInSurvey(): void
    {

        $questionGroup = new QuestionGroup();

        $groups = $questionGroup->getAllGroups('');
        $aGroups = $groups->readAll();

        $itDescription = $questionGroup->getGroupDescription($aGroups[0]['gid'], 'it');

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

        $questionGroup = new QuestionGroup();

        $groups = $questionGroup->getAllGroups('');
        $aGroups = $groups->readAll();

        $frDescription = $questionGroup->getGroupDescription($aGroups[1]['gid'], 'fr');

        // English was set as the language by default.
        $defaultDescription = $questionGroup->getGroupDescription($aGroups[1]['gid'], 'en');

        $this->assertSame($defaultDescription, $frDescription, 'No description in French was defined, the function should return the description in English.');
    }

    /**
     * Test for descriptions defined.
     */
    public function testDescriptionsSet(): void
    {

        $questionGroup = new QuestionGroup();

        $groups = $questionGroup->getAllGroups('');
        $aGroups = $groups->readAll();

        $enDescriptionGroupOne = 'This is the description for the first test question group.';
        $esDescriptionGroupOne = 'Esta es la descripción para el primer grupo de preguntas de prueba.';

        $enDescriptionGroupTwo = 'This is the description for the second question group.';
        $esDescriptionGroupTwo = 'Esta es la descripción para el segundo grupo de preguntas de prueba.';

        $enSetDescriptionGroupOne = $questionGroup->getGroupDescription($aGroups[0]['gid'], 'en');
        $esSetDescriptionGroupOne = $questionGroup->getGroupDescription($aGroups[0]['gid'], 'es');

        $enSetDescriptionGroupTwo = $questionGroup->getGroupDescription($aGroups[1]['gid'], 'en');
        $esSetDescriptionGroupTwo = $questionGroup->getGroupDescription($aGroups[1]['gid'], 'es');

        $this->assertSame($enDescriptionGroupOne, $enSetDescriptionGroupOne, 'The English description for group one is not correct.');
        $this->assertSame($esDescriptionGroupOne, $esSetDescriptionGroupOne, 'The Spanish description for group one is not correct.');
        $this->assertSame($enDescriptionGroupTwo, $enSetDescriptionGroupTwo, 'The English description for group two is not correct.');
        $this->assertSame($enDescriptionGroupTwo, $enSetDescriptionGroupTwo, 'The Spanish description for group two is not correct.');
    }
}
