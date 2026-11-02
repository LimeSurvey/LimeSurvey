<?php

namespace ls\tests;

use LimeSurvey\Datavalueobjects\SimpleSurveyValues;
use LimeSurvey\Models\Services\CreateSurvey;
use PHPUnit\Framework\TestCase;

class CreateSurveyServiceTest extends TestCase
{
    /**
     * Creates a simple survey
     */
    public function testCreateSurvey(){
        $newLanguageSettingMock = $this->createMock(\SurveyLanguageSetting::class);
        $newLanguageSettingMock->method('insertNewSurvey')->willReturn(true);

        $surveyMock = $this
            ->getMockBuilder(\Survey::class)
            ->setMethods(
                [
                    'validate',
                    'save',
                    'attributes'
                ]
            )
            ->getMock();
        $surveyMock->method('validate')->willReturn(true);
        $surveyMock->method('save')->willReturn(true);
        $surveyMock->method('attributes')->willReturn(
            [
                'sid'
            ]
        );
        $surveyMock->sid = 1;

        $createSurveyServiceClass = new CreateSurvey($surveyMock, $newLanguageSettingMock);
        $simpleValues =  new SimpleSurveyValues();
        $simpleValues->baseLanguage = 'en';
        $simpleValues->surveyGroupId = '1'; //must exists (this is default, means always exists)
        $simpleValues->title = 'myNewTestSurvey';

        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadHelper("surveytranslator");

        $userId = 1; //this id always exists (it's the admin id ....)

        $permissionModel =  \Permission::model();

        self::assertNotFalse($createSurveyServiceClass->createSimple($simpleValues, $userId, $permissionModel));
        self::assertSame('', $createSurveyServiceClass->getErrorMessage());
    }

    /**
     * A database error gives a generic reason, without the details of the exception
     */
    public function testCreateSurveyDatabaseError()
    {
        $surveyMock = $this->getSurveyMock();
        $surveyMock->method('save')->willThrowException(new \CDbException('Unknown column lastmodified'));

        $createSurveyServiceClass = new CreateSurvey($surveyMock, $this->createMock(\SurveyLanguageSetting::class));

        self::assertFalse($createSurveyServiceClass->createSimple($this->getSimpleValues('en'), 1, \Permission::model()));
        self::assertSame(
            gT("Survey could not be created because of a database error."),
            $createSurveyServiceClass->getErrorMessage()
        );
    }

    /**
     * Validation errors are part of the reason
     */
    public function testCreateSurveyInvalidValues()
    {
        $surveyMock = $this->getSurveyMock();
        $surveyMock->method('save')->willReturn(false);
        $surveyMock->method('getErrors')->willReturn(['adminemail' => ['Admin email is invalid.']]);

        $createSurveyServiceClass = new CreateSurvey($surveyMock, $this->createMock(\SurveyLanguageSetting::class));

        self::assertFalse($createSurveyServiceClass->createSimple($this->getSimpleValues('en'), 1, \Permission::model()));
        self::assertSame(
            sprintf(gT("Survey could not be created because some values are invalid: %s"), 'Admin email is invalid.'),
            $createSurveyServiceClass->getErrorMessage()
        );
    }

    /**
     * An unknown base language gives its own reason
     */
    public function testCreateSurveyInvalidLanguage()
    {
        $surveyMock = $this->getSurveyMock();
        $surveyMock->expects($this->never())->method('save');

        $createSurveyServiceClass = new CreateSurvey($surveyMock, $this->createMock(\SurveyLanguageSetting::class));

        self::assertFalse($createSurveyServiceClass->createSimple($this->getSimpleValues('xx-invalid'), 1, \Permission::model()));
        self::assertSame(
            gT("Survey could not be created because the base language is invalid."),
            $createSurveyServiceClass->getErrorMessage()
        );
    }

    /**
     * Returns a survey mock with a valid survey ID, save() and getErrors() are left to the test
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Survey
     */
    private function getSurveyMock()
    {
        $surveyMock = $this
            ->getMockBuilder(\Survey::class)
            ->setMethods(['validate', 'save', 'getErrors'])
            ->getMock();
        $surveyMock->method('validate')->willReturn(true);
        return $surveyMock;
    }

    /**
     * Returns the values for a simple survey
     *
     * @param string $baseLanguage
     * @return SimpleSurveyValues
     */
    private function getSimpleValues($baseLanguage)
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadHelper("surveytranslator");

        $simpleValues = new SimpleSurveyValues();
        $simpleValues->baseLanguage = $baseLanguage;
        $simpleValues->surveyGroupId = 1;
        $simpleValues->title = 'myNewTestSurvey';
        return $simpleValues;
    }
}
