<?php
namespace ls\models\forms;

class FormattingOptions extends \CFormModel
{
    public $type = 'csv';

    protected $_surveyId;
    /**
    * The columns that have been selected for output.  The values must be
    * in fieldMap format.
    *
    * @var string[]
    */
    public $selectedColumns = [];

    /**
    * Acceptable values are:
    * "complete" = include only incomplete answers
    * "incomplete" = only include incomplete answers
    * "all" = include ALL answers
    *
    * @var mixed
    */
    public $responseCompletionState;

    /**
    * Acceptable values are:
    * "abbreviated" = Abbreviated headings
    * "full" = Full headings
    * "code" = ls\models\Question codes
    * "none" = No headers.
    *
    * @var string
    */
    public $headingFormat;

    /**
    * Indicates whether to convert spaces in question headers to underscores.
    *
    * @var boolean
    */
    public $headerSpacesToUnderscores;

    /**
    * Indicates whether to ellipsize each text part to.
    *
    * @var integer
    */
    public $headingTextLength;

    /**
    * Indicates whether to use Expression Manager code
    *
    * @var bolean
    */
    public $useEMCode;

    /**
    * What is the caracters to separate code and text
    *
    * @var bolean
    */
    public $headCodeTextSeparator;

    /**
    * Valid values are:
    * "short" = ls\models\Answer codes
    * "long" = Full answers
    *
    * @var string
    */
    public $answerFormat;

    public $yValue = 'Y';

    public $nValue = 'N';
    
    public $offset = 0;
    public $limit;


    public function attributeLabels() {
        return [

        ];
    }


    /**
     * Use setter so we can auto select all columns.
     * @param $value
     */
    public function setSurveyId($value) {
        $this->_surveyId = $value;
        $this->selectedColumns = array_keys($this->getSelectedColumnOptions());
    }

    public function getSurveyId() {
        return $this->_surveyId;
    }
    public function toString()
    {
        return $this->format.','.$this->headingFormat.','
        .$this->headerSpacesToUnderscores.','.$this->responseCompletionState
        .','.$this->responseMinRecord.','.$this->responseMaxRecord.','
        .$this->answerFormat.','.$this->convertY.','.$this->yValue.','
        .$this->convertN.','.$this->nValue.','
        .implode(',',$this->selectedColumns);
    }



    public function rules() {
        /**
         * @todo Add proper validation rules.
         */
        return [
            [['responseCompletionState',
                'headerSpacesToUnderscores', 'headingTextLength', 'useEMCode', 'headCodeTextSeparator', 'answerFormat', 'convertY', 'yValue',
                'convertN', 'nValue', 'output'
            ], 'safe'],

            [['offset', 'limit'], 'numerical'],
            ['headingFormat', 'in', 'range' => array_keys($this->getHeadingFormatOptions())],

            // We need a subsetvalidator instead of range, since selectedColumns is an array.
            //['selectedColumns', 'in', 'range' => array_keys($this->getSelectedColumnOptions())],
            ['type', 'in', 'range' => array_keys($this->getTypeOptions())],
            [['selectedColumns', 'type'], 'required'],
            ['headerSpacesToUnderscores', 'boolean'],

        ];

    }

    public function getSelectedColumnOptions() {
        $columns = array_keys(\ls\models\Survey::model()->findByPk($this->surveyId)->getColumns());
        return array_combine($columns, $columns);
    }

    public function getHeadingFormatOptions() {
        return [
            "code" => gT('ls\models\Question code'),
            "abbreviated" => gT('Abbreviated question text'),
            "full" => gT("Full question text"),
            "none" => gT("No headers")
        ];
    }

    public function getResponseCompletionStateOptions() {
        return [
            "complete" => gT("Completed responses only"),
            "incomplete" => gT("Incomplete responses only"),
            "all" => gT("All responses")
        ];
    }


    public function getAnswerFormatOptions() {
        return [
            "short" => gT("ls\models\Answer codes"),
            "long" => gT('Full answers')
        ];
    }

    public function getTypeOptions() {
        $event = new \PluginEvent('listExportPlugins');
        $event->dispatch();
        $result = [];
        foreach ($event->get('exportplugins', []) as $type => $details) {
            $result[$type] = $details['label'];
        };
        return $result;
    }

    /**
     * @return \IWriter
     */
    public function getWriter() {
        $event = new \PluginEvent('newExport');
        $event->set('type', $this->type);
        $event->dispatch();
        return $event->get('writer');
    }
}
