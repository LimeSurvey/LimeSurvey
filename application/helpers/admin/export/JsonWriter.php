<?php
class JsonWriter extends Writer
{
    private $output;
    /**
     * The open filehandle
     */
    private $file = null;
    /**
     * first don't need seperator
     */
    protected $havePrev = false;

    function __construct()
    {
        $this->output = '';
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $sStartOutput = '{'.json_encode("responses").': [';
        if ($oOptions->output == 'display') {
            header("Content-type: application/json");
            echo $sStartOutput;
        } elseif ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
            if ($this->file !== false) {
                fwrite($this->file, $sStartOutput);
            } else {
                safeDie('Could not open JSON file');
            }
        }
        
    }
    
    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $aJson = [];
        $aJson[$values[0]] = array_combine($headers, $values);
        $sJson = json_encode($aJson);
        Yii::log($this->havePrev, 'info', 'info');
        if ($this->havePrev) {
            $sJson = ','.$sJson;

        }
        $this->havePrev = true;
        if ($oOptions->output == 'display') {
            echo $sJson;
            $this->output = '';
        } elseif ($oOptions->output == 'file') {
            $this->output .= $sJson;
            fwrite($this->file, $this->output);
            $this->output = '';
        }

    }

    public function close()
    {
        $sEndOutput = ']}';
        if (!$this->file) {
            echo $sEndOutput;
        } else {
            $this->output .= $sEndOutput;
            fwrite($this->file, $this->output);
            fclose($this->file);
        }
    }
}
