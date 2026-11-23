<?php

namespace ls\tests;

use Token;

/**
 * Tests for the base class of the dynamic models (Token, Response, Timing).
 */
class DynamicModelTest extends TestBaseClass
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
     * Form fields and grid parameters are named after the base class, not after the generated class.
     *
     * @return void
     */
    public function testHtmlNameIsTheBaseClassName()
    {
        $model = Token::model(self::$surveyId);

        $this->assertSame('Token_' . self::$surveyId, get_class($model));
        $this->assertSame('Token', \Dynamic::htmlModelName($model));
        $this->assertSame('Token', \Dynamic::htmlModelName(get_class($model)));
        $this->assertSame('Token[email]', \CHtml::activeName($model, 'email'));
        $this->assertSame('Survey', \Dynamic::htmlModelName(self::$testSurvey), 'Other models keep their class name.');
    }

    /**
     * model() and create() also work when called on a generated class.
     *
     * @return void
     */
    public function testModelAndCreateFromGeneratedClass()
    {
        $className = 'Token_' . self::$surveyId;

        $this->assertSame($className, get_class($className::model(self::$surveyId)));
        $this->assertSame($className, get_class($className::create(self::$surveyId)));
    }

    /**
     * A column added to the table is known to the model as soon as the schema cache was refreshed.
     *
     * @return void
     */
    public function testModelKnowsColumnAddedToTheTable()
    {
        $tableName = '{{tokens_' . self::$surveyId . '}}';
        $this->assertFalse(Token::model(self::$surveyId)->hasAttribute('attribute_42'));

        \Yii::app()->db->createCommand()->addColumn($tableName, 'attribute_42', 'text');
        \Yii::app()->db->schema->getTable($tableName, true);

        $this->assertTrue(Token::model(self::$surveyId)->hasAttribute('attribute_42'));
        $token = Token::create(self::$surveyId);
        $token->attribute_42 = 'value';
        $token->token = 'dynamicmodeltest';
        $this->assertTrue($token->save(false));
        $this->assertSame('value', Token::model(self::$surveyId)->findByToken('dynamicmodeltest')->attribute_42);
    }

    /**
     * Numeric strings are normalized, so they use the same class as the integer ID.
     *
     * @return void
     */
    public function testNumericStringIdUsesTheSameClass()
    {
        $className = 'Token_' . self::$surveyId;

        $this->assertSame($className, get_class(Token::model('0' . self::$surveyId)));
        $this->assertSame($className, get_class(Token::create((string) self::$surveyId)));
    }

    /**
     * A model that was requested before its table existed works once the table was created.
     *
     * @return void
     */
    public function testModelWorksAfterItsTableWasCreated()
    {
        $tableName = '{{timings_' . self::$surveyId . '}}';
        try {
            \Timing::model(self::$surveyId);
            $this->fail('The model of a missing table should not be available.');
        } catch (\CDbException $exception) {
            // Expected: The table does not exist yet
        }

        \Yii::app()->db->createCommand()->createTable($tableName, ['id' => 'pk', 'interviewtime' => 'float']);
        \Yii::app()->db->schema->getTable($tableName, true);

        $this->assertSame(0, (int) \Timing::model(self::$surveyId)->count());
        $this->assertTrue(\Timing::model(self::$surveyId)->hasAttribute('interviewtime'));
        $this->assertNotNull(\Timing::model(self::$surveyId)->asa('CTimestampBehavior'), 'The behaviors are attached now.');
        // Needs the plugin event behavior
        $this->assertSame(0, \Timing::model(self::$surveyId)->deleteAllByAttributes(['id' => 1]));
    }
}
