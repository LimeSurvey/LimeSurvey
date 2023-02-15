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
    }
}
