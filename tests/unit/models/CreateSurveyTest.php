<?php


namespace ls\tests;


use LimeSurvey\Datavalueobjects\SimpleSurveyValues;
use LimeSurvey\Models\Services\CreateSurvey;
use PHPUnit\Framework\TestCase;



class CreateSurveyTest extends TestCase
{

    /**
     * Creates a simple survey
     */
    public function testCreateSurvey(){
        $createSurveyServiceClass = new CreateSurvey(new \Survey(), new \SurveyLanguageSetting());
        $simpleValues =  new SimpleSurveyValues();
        $simpleValues->setBaseLanguage('en');
        $simpleValues->setSurveyGroupId('1'); //must exists (this is default, means always exists)
        $simpleValues->setTitle('myNewTestSurvey');


        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadHelper("surveytranslator");

        $userId = 1; //this id always exists (it'S the admin id ....)

        $permissionModel =  \Permission::model();

        self::assertNotFalse($createSurveyServiceClass->createSimple($simpleValues, $userId, $permissionModel));
    }

}