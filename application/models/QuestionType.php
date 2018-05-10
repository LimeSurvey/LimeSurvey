<?php

/**
 * Class QuestionType
 * @property Question $question
 * @property string $fieldType The type of field question needs for storing data
 * @property string $fieldDataType numeric vs string types
 * @property boolean $isText Whether the type is text (string longer than char)
 * @property boolean $isChar Whether the type is char (one-character-string)
 * @property boolean $isString Whether the type is string (text or char)
 * @property boolean $isNumeric Whether the type numeric (integer, double)
 * @property boolean $isInteger Whether the type integer
 *
 * {@inheritdoc}
 */
class QuestionType extends CModel
{
    const QT_1_ARRAY_MULTISCALE = '1'; //ARRAY (Flexible Labels) multi scale
    const QT_5_POINT_CHOICE = '5';
    const QT_A_ARRAY_5_CHOICE_QUESTIONS = 'A'; // ARRAY OF 5 POINT CHOICE QUESTIONS
    const QT_B_ARRAY_10_CHOICE_QUESTIONS = 'B'; // ARRAY OF 10 POINT CHOICE QUESTIONS
    const QT_C_ARRAY_YES_UNCERTAIN_NO = 'C'; // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
    const QT_D_DATE = 'D';
    const QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS = 'E';
    const QT_F_ARRAY_FLEXIBLE_ROW = 'F';
    const QT_G_GENDER_DROPDOWN = 'G';
    const QT_H_ARRAY_FLEXIBLE_COLUMN = 'H';
    const QT_I_LANGUAGE = 'I';
    const QT_K_MULTIPLE_NUMERICAL_QUESTION = 'K';
    const QT_L_LIST_DROPDOWN = 'L';
    const QT_M_MULTIPLE_CHOICE = 'M';
    const QT_N_NUMERICAL = 'N';
    const QT_O_LIST_WITH_COMMENT = 'O';
    const QT_P_MULTIPLE_CHOICE_WITH_COMMENTS = 'P';
    const QT_Q_MULTIPLE_SHORT_TEXT = 'Q';
    const QT_R_RANKING_STYLE = 'R';
    const QT_S_SHORT_FREE_TEXT = 'S';
    const QT_T_LONG_FREE_TEXT = 'T';
    const QT_U_HUGE_FREE_TEXT = 'U';
    const QT_X_BOILERPLATE_QUESTION = 'X';
    const QT_Y_YES_NO_RADIO = 'Y';
    const QT_Z_LIST_RADIO_FLEXIBLE = 'Z';
    const QT_EXCLAMATION_LIST_DROPDOWN = '!';
    const QT_VERTICAL_FILE_UPLOAD = '|';
    const QT_ASTERISK_EQUATION = '*';
    const QT_COLON_ARRAY_MULTI_FLEX_NUMBERS = ':';
    const QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT = ';';


    /** @var string $code */
    public $code;

    /** @var string $label */
    public $label;

    /**
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }


    /**
     * {@inheritdoc}
     */
    public function attributeNames()
    {
        return [
            'code' => gT("Code"),
            'label' => gT("Label"),
        ];
    }


    /**
     * Get all type codes of that represent data in text (string longer than char)
     * @return string[]
     */
    public static function textTypes()
    {
        return [
            self::QT_I_LANGUAGE, self::QT_S_SHORT_FREE_TEXT, self::QT_U_HUGE_FREE_TEXT,
            self::QT_Q_MULTIPLE_SHORT_TEXT, self::QT_T_LONG_FREE_TEXT, self::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT,
            self::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS,
        ];
    }



    /**
     * Get all type codes of that represent data in text (string longer than char)
     * @return string[]
     */
    public static function charCodes()
    {
        return [
            self::QT_5_POINT_CHOICE, self::QT_G_GENDER_DROPDOWN, self::QT_Y_YES_NO_RADIO,
            self::QT_X_BOILERPLATE_QUESTION
        ];
    }

    /**
     * Get all type codes of that represent data in string (text and char)
     * @return string[]
     */
    public static function stringCodes()
    {
        return array_merge(self::textTypes(), self::charCodes());
    }



    /**
     * Get all type codes of that represent data as integer
     * @return string[]
     */
    public static function integerCodes()
    {
        return [
            self::QT_VERTICAL_FILE_UPLOAD
        ];

    }

    /**
     * Get all type codes of that represent data as double
     * @return string[]
     */
    public static function doubleCodes()
    {
        return [];

    }

    /**
     * Get all type codes of that represent data as double
     * @return string[]
     */
    public static function numericCodes()
    {
        return array_merge(self::integerCodes(), self::doubleCodes());
    }

    /**
     * @return bool
     */
    public function getIsText()
    {
        return in_array($this->code, self::textTypes());
    }

    /**
     * @return bool
     */
    public function getIsChar()
    {
        return in_array($this->code, self::charCodes());
    }

    /**
     * @return bool
     */
    public function getIsString()
    {
        return in_array($this->code, self::charCodes());
    }

    /**
     * @return bool
     */
    public function getIsInteger()
    {
        return in_array($this->code, self::integerCodes());
    }

    /**
     * @return bool
     */
    public function getIsNumeric()
    {
        return in_array($this->code, self::numericCodes());
    }


    /**
     * @return string
     */
    public function getFieldType()
    {
        if ($this->isString) {
            if ($this->isChar) {
                return Field::TYPE_CHAR;
            } else {
                return Field::TYPE_STRING;
            }
        }
        return $this->getFieldDataType();

    }


    /**
     * @return string
     */
    public function getFieldDataType()
    {
        if ($this->isString) {
            return Field::TYPE_STRING;
        }
        if ($this->isInteger) {
            return Field::TYPE_INTEGER;
        }

        throw new \Exception("Undefined field data type for QuestionType {$this->code}");
    }

}