<?php

/**
 * Class QuestionType
 * @property Question $question
 * @property QuestionRenderer $questionRenderer
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
class QuestionType extends StaticModel
{
    const QT_1_ARRAY_DUAL = '1'; // Array dual scale
    const QT_5_POINT_CHOICE = '5';
    const QT_A_ARRAY_5_POINT = 'A'; // Array of 5 point choice questions
    const QT_B_ARRAY_10_POINT = 'B'; // Array of 10 point choice questions
    const QT_C_ARRAY_YES_UNCERTAIN_NO = 'C'; // ARRAY OF Yes\No\Uncertain questions
    const QT_D_DATE = 'D';
    const QT_E_ARRAY_INC_SAME_DEC = 'E';
    const QT_F_ARRAY = 'F';
    const QT_G_GENDER = 'G';
    const QT_H_ARRAY_COLUMN = 'H';
    const QT_I_LANGUAGE = 'I';
    const QT_K_MULTIPLE_NUMERICAL = 'K';
    const QT_L_LIST = 'L';
    const QT_M_MULTIPLE_CHOICE = 'M';
    const QT_N_NUMERICAL = 'N';
    const QT_O_LIST_WITH_COMMENT = 'O';
    const QT_P_MULTIPLE_CHOICE_WITH_COMMENTS = 'P';
    const QT_Q_MULTIPLE_SHORT_TEXT = 'Q';
    const QT_R_RANKING = 'R';
    const QT_S_SHORT_FREE_TEXT = 'S';
    const QT_T_LONG_FREE_TEXT = 'T';
    const QT_U_HUGE_FREE_TEXT = 'U';
    const QT_X_TEXT_DISPLAY = 'X';
    const QT_Y_YES_NO_RADIO = 'Y';
    const QT_EXCLAMATION_LIST_DROPDOWN = '!';
    const QT_VERTICAL_FILE_UPLOAD = '|';
    const QT_ASTERISK_EQUATION = '*';
    const QT_COLON_ARRAY_NUMBERS = ':';
    const QT_SEMICOLON_ARRAY_TEXT = ';';

    /*
    * @deprecated The following constants are deprecated and will be removed in LimeSurvey 6 - please use the ones above.
    */
    const QT_1_ARRAY_MULTISCALE = '1'; //ARRAY (Flexible Labels) multi scale
    const QT_A_ARRAY_5_CHOICE_QUESTIONS = 'A'; // ARRAY OF 5 POINT CHOICE QUESTIONS
    const QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS = 'E';
    const QT_F_ARRAY_FLEXIBLE_ROW = 'F';
    const QT_G_GENDER_DROPDOWN = 'G';
    const QT_H_ARRAY_FLEXIBLE_COLUMN = 'H';
    const QT_K_MULTIPLE_NUMERICAL_QUESTION = 'K';
    const QT_R_RANKING_STYLE = 'R';
    const QT_X_BOILERPLATE_QUESTION = 'X';
    const QT_COLON_ARRAY_MULTI_FLEX_NUMBERS = ':';
    const QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT = ';';


    /** @var Question */
    public $question;

    /** @var string $code */
    public $code;

    /** @var string $description */
    public $description;

    /** @var string $group Group name*/
    public $group;

    /** @var integer $subquestions whether has subquestions //TODO make it boolean instead */
    public $subquestions;

    /** @var boolean $other allow other (and add subquetsion title other if Y)*/
    public $other = false;

    /** @var integer $assessable whether it can be used inside Assessments / Quizmode */
    public $assessable;

    /** @var integer $ahasdefaultvalues whether has default values */
    public $hasdefaultvalues;

    /** @var integer $answerscales number of answer scales*/
    public $answerscales;

    /** @var string $class the css class for question (container??)*/
    public $class;



    /**
     * {@inheritdoc}
     */
    public function attributeNames()
    {
        return [
            'code',
            'description',
            'group',
            'subquestions',
            'other',
            'assessable',
            'hasdefaultvalues',
            'answerscales',
            'class'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => gT("Code"),
            'description' => gT("Description"),
            'group' => gT("Group"),
        ];
    }

    public function applyToQuestion($oQuestion)
    {
        $this->question = $oQuestion;
        $aSettingsArray = self::modelsAttributes($oQuestion->survey->language)[$oQuestion->type];
        foreach ($aSettingsArray as $settingKey => $setting) {
            $this->$settingKey = $setting;
        }
    }

    /**
     * @param string $language
     * @return array
     * Still used in QuestionAdministrationController
     *
     * TODO choose between self::modelsAttributes and QuestionTheme::findQuestionMetaData or QuestionTheme::getAllQuestionMetaData
     * TODO QuestionTheme 1591616914305: Needs to be replaced by @link QuestionTheme::getAllQuestionMetaData() however translations inside the xml need to be inserted first
     */

    public static function modelsAttributes($language = '')
    {
        return [
            self::QT_1_ARRAY_DUAL => [
                'code' => self::QT_1_ARRAY_DUAL,
                'description' => gT("Array dual scale", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'assessable' => 1,
                'hasdefaultvalues' => 0,
                'answerscales' => 2,
                'class' => 'array-flexible-dual-scale',
            ],
            self::QT_5_POINT_CHOICE => [
                'code' => self::QT_5_POINT_CHOICE,
                'description' => gT("5 point choice", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => "choice-5-pt-radio"
            ],
            self::QT_A_ARRAY_5_POINT => [
                'code' => self::QT_A_ARRAY_5_POINT,
                'description' => gT("Array (5 point choice)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-5-pt'
            ],
            self::QT_B_ARRAY_10_POINT => [
                'code' => self::QT_B_ARRAY_10_POINT,
                'description' => gT("Array (10 point choice)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-10-pt'
            ],
            self::QT_C_ARRAY_YES_UNCERTAIN_NO => [
                'code' => self::QT_C_ARRAY_YES_UNCERTAIN_NO,
                'description' => gT("Array (Yes/No/Uncertain)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-yes-uncertain-no'
            ],
            self::QT_D_DATE => [
                'code' => self::QT_D_DATE,
                'description' => gT("Date/Time", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'date'
            ],
            self::QT_E_ARRAY_INC_SAME_DEC => [
                'code' => self::QT_E_ARRAY_INC_SAME_DEC,
                'description' => gT("Array (Increase/Same/Decrease)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-increase-same-decrease'
            ],
            self::QT_F_ARRAY => [
                'code' => self::QT_F_ARRAY,
                'description' => gT("Array", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'array-flexible-row'
            ],
            self::QT_G_GENDER => [
                'code' => self::QT_G_GENDER,
                'description' => gT("Gender", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'gender'
            ],
            self::QT_H_ARRAY_COLUMN => [
                'code' => self::QT_H_ARRAY_COLUMN,
                'description' => gT("Array by column", "html", $language),
                'group' => gT('Arrays'),
                'hasdefaultvalues' => 0,
                'subquestions' => 1,
                'other' => false,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'array-flexible-column'
            ],
            self::QT_I_LANGUAGE => [
                'code' => self::QT_I_LANGUAGE,
                'description' => gT("Language switch", "html", $language),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 0,
                'subquestions' => 0,
                'other' => false,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'language'
            ],
            self::QT_K_MULTIPLE_NUMERICAL => [
                'code' => self::QT_K_MULTIPLE_NUMERICAL,
                'description' => gT("Multiple numerical input", "html", $language),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 1,
                'subquestions' => 1,
                'other' => false,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'numeric-multi'
            ],
            self::QT_L_LIST => [
                'code' => self::QT_L_LIST,
                'description' => gT("List (Radio)", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'other' => true,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-radio'
            ],
            self::QT_M_MULTIPLE_CHOICE => [
                'code' => self::QT_M_MULTIPLE_CHOICE,
                'description' => gT("Multiple choice", "html", $language),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'other' => true,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'multiple-opt'
            ],
            self::QT_N_NUMERICAL => [
                'code' => self::QT_N_NUMERICAL,
                'description' => gT("Numerical input", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'numeric'
            ],
            self::QT_O_LIST_WITH_COMMENT => [
                'code' => self::QT_O_LIST_WITH_COMMENT,
                'description' => gT("List with comment", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-with-comment'
            ],
            self::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS => [
                'code' => self::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS,
                'description' => gT("Multiple choice with comments", "html", $language),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'other' => true,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'multiple-opt-comments'
            ],
            self::QT_Q_MULTIPLE_SHORT_TEXT => [
                'code' => self::QT_Q_MULTIPLE_SHORT_TEXT,
                'description' => gT("Multiple short text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 1,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'multiple-short-txt'
            ],
            self::QT_R_RANKING => [
                'code' => self::QT_R_RANKING,
                'description' => gT("Ranking", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'ranking'
            ],
            self::QT_S_SHORT_FREE_TEXT => [
                'code' => self::QT_S_SHORT_FREE_TEXT,
                'description' => gT("Short free text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-short'
            ],
            self::QT_T_LONG_FREE_TEXT => [
                'code' => self::QT_T_LONG_FREE_TEXT,
                'description' => gT("Long free text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-long'
            ],
            self::QT_U_HUGE_FREE_TEXT => [
                'code' => self::QT_U_HUGE_FREE_TEXT,
                'description' => gT("Huge free text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-huge'
            ],
            self::QT_X_TEXT_DISPLAY => [
                'code' => self::QT_X_TEXT_DISPLAY,
                'description' => gT("Text display", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'boilerplate'
            ],
            self::QT_Y_YES_NO_RADIO => [
                'code' => self::QT_Y_YES_NO_RADIO,
                'description' => gT("Yes/No", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'yes-no'
            ],
            self::QT_EXCLAMATION_LIST_DROPDOWN => [
                'code' => self::QT_EXCLAMATION_LIST_DROPDOWN,
                'description' => gT("List (Dropdown)", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'other' => true,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-dropdown'
            ],
            self::QT_COLON_ARRAY_NUMBERS => [
                'code' => self::QT_COLON_ARRAY_NUMBERS,
                'description' => gT("Array (Numbers)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-multi-flexi'
            ],
            self::QT_SEMICOLON_ARRAY_TEXT => [
                'code' => self::QT_SEMICOLON_ARRAY_TEXT,
                'description' => gT("Array (Texts)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'array-multi-flexi-text'
            ],
            self::QT_VERTICAL_FILE_UPLOAD => [
                'code' => self::QT_VERTICAL_FILE_UPLOAD,
                'description' => gT("File upload", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'upload-files'
            ],
            self::QT_ASTERISK_EQUATION => [
                'code' => self::QT_ASTERISK_EQUATION,
                'description' => gT("Equation", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'other' => false,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'equation'
            ],
        ];
    }


    /**
     * Get all type codes of that represent data in text (string longer than char)
     * @return string[]
     */
    public static function textCodes()
    {
        return [
            self::QT_I_LANGUAGE, self::QT_S_SHORT_FREE_TEXT, self::QT_U_HUGE_FREE_TEXT,
            self::QT_Q_MULTIPLE_SHORT_TEXT, self::QT_T_LONG_FREE_TEXT, self::QT_SEMICOLON_ARRAY_TEXT,
            self::QT_COLON_ARRAY_NUMBERS,
        ];
    }


    /**
     * Get all type codes of that represent data in char (one-character-string)
     * @return string[]
     */
    public static function charCodes()
    {
        return [
            self::QT_5_POINT_CHOICE, self::QT_G_GENDER, self::QT_Y_YES_NO_RADIO,
            self::QT_X_TEXT_DISPLAY
        ];
    }

    /**
     * Get all type codes of that represent data in string (text and char)
     * @return string[]
     */
    public static function stringCodes()
    {
        return array_merge(self::textCodes(), self::charCodes());
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
        return in_array($this->code, self::textCodes());
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
                return 'char';
            } else {
                return 'string';
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
            return 'string';
        }
        if ($this->isInteger) {
            return 'integer';
        }

        throw new \Exception("Undefined field data type for QuestionType {$this->code}");
    }
}
