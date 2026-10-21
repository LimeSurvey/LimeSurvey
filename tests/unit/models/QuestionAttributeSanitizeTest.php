<?php

namespace ls\tests;

/**
 * Question attribute values are sanitized according to the input type of their definition.
 */
class QuestionAttributeSanitizeTest extends TestBaseClass
{
    /** @var \Question Short free text question of the test survey */
    private static $question;

    /**
     * Imports a survey with a short free text question.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.admin.import_helper', true);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_141451_testSaveQuestionAttributes.lss');
        self::$question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q01']);
    }

    /**
     * @return array<string,array{string,string,string}> attribute name, submitted value, sanitized value
     */
    public static function valueProvider()
    {
        return [
            'integer: number' => ['display_rows', '5', '5'],
            'integer: number with spaces' => ['display_rows', ' 7 ', '7'],
            'integer: decimal number' => ['display_rows', '2.5', '2.5'],
            'integer: empty' => ['display_rows', '', ''],
            'integer: text' => ['display_rows', "It's not a number", ''],
            'integer: number followed by HTML special characters' => ['display_rows', '5"<>&', ''],
            'switch: 1' => ['hidden', '1', '1'],
            'switch: legacy Y' => ['hidden', 'Y', '1'],
            'switch: legacy N' => ['hidden', 'N', '0'],
            'switch: invalid value gets the default' => ['hidden', 'yes"<>', '0'],
            'single select: option' => ['text_input_width', '6', '6'],
            'single select: invalid value gets the default' => ['text_input_width', '6"<>', ''],
            'text: unchanged' => ['cssclass', 'some-class', 'some-class'],
            'unknown attribute: unchanged' => ['not_an_attribute', 'abc', 'abc'],
        ];
    }

    /**
     * @dataProvider valueProvider
     * @param string $attributeName
     * @param string $value
     * @param string $expected
     */
    public function testSanitizeByInputType($attributeName, $value, $expected)
    {
        $questionAttribute = new \QuestionAttribute();
        $questionAttribute->qid = self::$question->qid;
        $questionAttribute->attribute = $attributeName;
        $questionAttribute->value = $value;
        $questionAttribute->sanitizeByInputType('value', []);
        $this->assertSame($expected, $questionAttribute->value);
    }

    /**
     * @return array<string,array{mixed,array<string,mixed>,mixed}> value, definition, sanitized value
     */
    public static function definitionProvider()
    {
        return [
            'float' => ['0.5', ['inputtype' => 'float'], '0.5'],
            'columns: invalid' => ['2"<>', ['inputtype' => 'columns'], ''],
            'button group: option' => ['lg', ['inputtype' => 'buttongroup', 'options' => ['default' => 'Default', 'lg' => 'Large'], 'default' => 'default'], 'lg'],
            'button group: invalid' => ['lg"<>', ['inputtype' => 'buttongroup', 'options' => ['default' => 'Default', 'lg' => 'Large'], 'default' => 'default'], 'default'],
            'single select: plain list of options' => ['b', ['inputtype' => 'singleselect', 'options' => ['a', 'b']], 'b'],
            'single select: no options' => ['b"<>', ['inputtype' => 'singleselect', 'options' => null], 'b"<>'],
            'expression allowed: unchanged' => ['{Q1}', ['inputtype' => 'integer', 'expression' => 2], '{Q1}'],
            'not a string: unchanged' => [['en' => 'x'], ['inputtype' => 'integer'], ['en' => 'x']],
            'null: unchanged' => [null, ['inputtype' => 'integer'], null],
        ];
    }

    /**
     * @dataProvider definitionProvider
     * @param mixed $value
     * @param array<string,mixed> $definition
     * @param mixed $expected
     */
    public function testSanitizeValue($value, $definition, $expected)
    {
        $this->assertSame($expected, \QuestionAttribute::sanitizeValue($value, $definition));
    }

    /**
     * A non-numeric value of an integer attribute is not stored.
     */
    public function testSaveEmptiesInvalidInteger()
    {
        \QuestionAttribute::model()->setQuestionAttribute(self::$question->qid, 'display_rows', "It's not a number");
        $questionAttribute = \QuestionAttribute::model()->findByAttributes(
            ['qid' => self::$question->qid, 'attribute' => 'display_rows']
        );
        $this->assertNotNull($questionAttribute);
        $this->assertSame('', (string) $questionAttribute->value);
    }

    /**
     * Invalid values already stored in the database are sanitized when the attributes are read for the survey.
     */
    public function testStoredInvalidValuesAreSanitizedOnRead()
    {
        $qid = self::$question->qid;
        $db = \Yii::app()->db;
        $db->createCommand()->delete('{{question_attributes}}', "qid = :qid and attribute in ('display_rows', 'text_input_width')", [':qid' => $qid]);
        $db->createCommand()->insert('{{question_attributes}}', ['qid' => $qid, 'attribute' => 'display_rows', 'value' => '5"<>', 'language' => '']);
        $db->createCommand()->insert('{{question_attributes}}', ['qid' => $qid, 'attribute' => 'text_input_width', 'value' => '6"<>', 'language' => '']);

        $attributes = \QuestionAttribute::model()->getQuestionAttributes($qid);

        $this->assertSame('', $attributes['display_rows']);
        $this->assertSame('', $attributes['text_input_width']);
    }

    /**
     * Theme attribute definitions are read from both option formats of the theme configuration.
     */
    public function testCustomAttributeDefinitionOptions()
    {
        $method = new \ReflectionMethod(\QuestionTemplate::class, 'getCustomAttributeDefinition');
        $method->setAccessible(true);

        $shortFormat = simplexml_load_string(
            '<attribute><name>button_size</name><inputtype>buttongroup</inputtype>'
            . '<options><default>Default</default><lg>Large</lg></options><default>default</default></attribute>'
        );
        $definition = $method->invoke(null, $shortFormat);
        $this->assertSame(['default' => 'Default', 'lg' => 'Large'], $definition['options']);
        $this->assertSame('default', \QuestionAttribute::sanitizeValue('lg"<>', $definition));

        $longFormat = simplexml_load_string(
            '<attribute><name>size</name><inputtype>singleselect</inputtype><options>'
            . '<option><value>1</value><text>One</text></option><option><value>2</value><text>Two</text></option>'
            . '</options><default>1</default></attribute>'
        );
        $definition = $method->invoke(null, $longFormat);
        $this->assertSame([1 => 'One', 2 => 'Two'], $definition['options']);
        $this->assertSame('2', \QuestionAttribute::sanitizeValue('2', $definition));
    }

    /**
     * Attribute values of an imported question group are sanitized although they are inserted directly.
     */
    public function testGroupImportSanitizesValues()
    {
        $results = XMLImportGroup(
            self::$dataFolder . '/file_upload/limesurvey_group_19736_attribute_values.lsg',
            self::$surveyId,
            false
        );
        $this->assertArrayNotHasKey('fatalerror', $results);

        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'R2Q04']);
        $this->assertNotNull($question);
        $values = \CHtml::listData(
            \QuestionAttribute::model()->findAllByAttributes(['qid' => $question->qid]),
            'attribute',
            'value'
        );
        $this->assertSame('', (string) $values['display_rows']);
        $this->assertSame('1', (string) $values['hidden']);
    }
}
