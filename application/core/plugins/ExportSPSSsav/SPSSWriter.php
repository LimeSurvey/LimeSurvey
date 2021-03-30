<?php

/* Creates a file containing responses in the SAV (native SPSS binary format). Uses: https://github.com/tiamo/spss/ to do so
 * Use STATAs xmluse command to import. eg.: xmluse "\survey_844845_STATA.xml", doctype(dta)
 * In contrast to importing a plain CSV or xls-file, the data is fully labelled with variable- and value labels.
 * Date and time strings are converted to STATAs time format (milliseconds since 1960/01/01), so they can be directly used in calculations
 * Limitations:
 *  STATA versions 8 through 12? only support strings up to 244 bytes, version 13 up to 2045 bytes.....longer answers (ie. text fields) will be cut.
 *  STATA only supports attaching value labels to numerical values. So to achieve short answers (usually one or two digits) and
 *  have these properly labelled, one should use numerical answer-codes in LimeSurvey (1=Totally agree).
 *  If non-numerical answer codes are used (A=Totally agree), then the complete answer text will be used as answer (eg.: 'Totally agree').
 */

use SPSS\Sav\Variable;


class SPSSWriter extends Writer
{
    private $output;
    private $separator;
    private $hasOutputHeader;
    private $maxByte = 100; // max value of STATA byte var
    private $minByte = -127; // min value of STATA byte var
    private $maxInt = 32740; // max value of STATA int var
    private $minInt = -32767; // min value of STATA int var

    /**
     * The open filehandle
     */
    protected $handle = null;
    protected $customFieldmap = array();
    protected $customResponsemap = array();
    protected $headers = array();
    protected $headersSGQA = array();
    protected $aQIDnonumericalAnswers = array();

    protected $settings = array(
        'spssfileversion' => array(
            'type' => 'select',
            'label' => 'Export for SPSS',
            'options' => array('16' => 'versions 14 and above', '13'  => 'version 13 and below (limited string length)'),
            'default' => '16',
            'submitonchange'=> false
            )
        );


    function __construct($pluginsettings)
    {
        $this->output          = '';
        $this->separator       = ',';
        $this->hasOutputHeader = false;
        $this->spssfileversion = $pluginsettings['spssfileversion']['current'];
        if ($this->spssfileversion >= 13) {
            $this->maxStringLength = 32767; // for SPSS version 13 and above
        } else {
            $this->maxStringLength = 255; // for older SPSS versions
        }
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        if ($oOptions->output == 'display') {
            header("Content-Disposition: attachment; filename=survey_".$survey->id."_spss.sav");
            header("Content-type: application/download; charset=US-ASCII");
            header("Cache-Control: must-revalidate, no-store, no-cache");
            $this->handle = fopen('php://output', 'w');
        } elseif ($oOptions->output == 'file') {
            $this->handle = fopen($this->filename, 'w');
        }
        $this->headersSGQA       = $oOptions->selectedColumns;
        $oOptions->headingFormat = 'code'; // Always use fieldcodes

        $this->customFieldmap = $this->createSPSSFieldmap($survey, $sLanguageCode, $oOptions);

//var_dump($this->customFieldmap);
    }


    /**
     * @param string $content
     */
    protected function out($content)
    {
        fwrite($this->handle, $content."\n");
    }


    /* Returns an array with vars, labels, survey info
     * For SPSS sav files using, we basically need:
     * Header: Number of Variables, Number of observations, SurveyTitle, Timestamp
     * Typelist: code, STATA_datatype
     * Varlist: code
     * fmtlist: code, STATA_format
     * Lbllist: code, setname of valuelabels
     * variable_labels: code, vardescription (question text)
     * Data: ObservationNumber(ID), code, value
     * Valuelabels: Setname, Answercode, Answer
     *
     * Some things depending on the responses (eg. STATA data type and format, some reoding),
     * are done later in updateResponsemap()
     */

    /**
     * @param SurveyObj $survey
     * @param string $sLanguage
     * @param FormattingOptions $oOptions
     * @return mixed
     */
    function createSPSSFieldmap($survey, $sLanguage, $oOptions)
    {
        App()->setLanguage($sLanguage);

        $yvalue = $oOptions->convertY ? $oOptions->yValue : '1'; // set value for Y if it is set in export settings (needed for correct value label)
        $nvalue = $oOptions->convertN ? $oOptions->nValue : '2'; // set value for N if it is set in export settings (needed for correct value label)

        //create fieldmap only with the columns (variables) selected
        $aFieldmap['questions'] = array_intersect_key($survey->fieldMap, array_flip($oOptions->selectedColumns));

        //tokens need to be "smuggled" into the fieldmap as additional questions
        $aFieldmap['tokenFields'] = array_intersect_key($survey->tokenFields, array_flip($oOptions->selectedColumns));
        foreach ($aFieldmap['tokenFields'] as $key=>$value) {
            $aFieldmap['questions'][$key] = $value;
            $aFieldmap['questions'][$key]['qid'] = '';
            $aFieldmap['questions'][$key]['question'] = $value['description'];
            $aFieldmap['questions'][$key]['fieldname'] = $key;
            $aFieldmap['questions'][$key]['type'] = 'S';
        }
        // add only questions and answers to the fieldmap that are relevant to the selected columns (variables)
        foreach ($aFieldmap['questions'] as $question) {
            $aUsedQIDs[] = $question['qid'];
        }
        $aFieldmap['answers'] = array_intersect_key($survey->answers, array_flip($aUsedQIDs));

        // add per-survey info
        $aFieldmap['info'] = $survey->info;

        // SPSS can only use value labels on numerical variables. If the answer codes are not numerical we later replace them with the text-answer
        // here we go through the answers-array and check whether answer-codes are numerical. If they are not, we save the respective QIDs
        // so responses can later be set to full answer test of Question or SQ'
        foreach ($aFieldmap['answers'] as $qid => $aScale) {
            foreach ($aFieldmap['answers'][$qid] as $iScale => $aAnswers) {
                foreach ($aFieldmap['answers'][$qid][$iScale] as $iAnswercode => $aAnswer) {
                    if (!is_numeric($aAnswer['code'])) {
                        $this->aQIDnonumericalAnswers[$aAnswer['qid']] = true;
                    }
                }
            }
        }

        // go through the questions array and create/modify vars for SPSS-output
        foreach ($aFieldmap['questions'] as $sSGQAkey => $aQuestion) {
            // SPSS does not support attaching value labels to non-numerical values
            // We therefore set a flag in questions array for non-numerical answer codes.
            // The respective codes are later recoded to contain the full answers
            if (array_key_exists($aQuestion['qid'], $this->aQIDnonumericalAnswers)) {
                $aFieldmap['questions'][$sSGQAkey]['nonnumericanswercodes'] = true;
            } else {
                $aFieldmap['questions'][$sSGQAkey]['nonnumericanswercodes'] = false;
            }


            // create 'varname' from Question/Subquestiontitles
            $aQuestion['varname'] = viewHelper::getFieldCode($aFieldmap['questions'][$sSGQAkey]);

            //set field types for standard vars
            if ($aQuestion['varname'] == 'submitdate' || $aQuestion['varname'] == 'startdate' || $aQuestion['varname'] == 'datestamp') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'D';
            } elseif ($aQuestion['varname'] == 'startlanguage') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'S';
            } elseif ($aQuestion['varname'] == 'token') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'S';
            } elseif ($aQuestion['varname'] == 'id') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'N';
            } elseif ($aQuestion['varname'] == 'ipaddr') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'S';
            } elseif ($aQuestion['varname'] == 'refurl') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'S';
            } elseif ($aQuestion['varname'] == 'lastpage') {
                $aFieldmap['questions'][$sSGQAkey]['type'] = 'N';
            }


            //Rename the variables if original name is not STATA-compatible
            $aQuestion['varname'] = $this->SPSSvarname($aQuestion['varname']);

            // create variable labels
            $aQuestion['varlabel'] = $aQuestion['question'];
            if (isset($aQuestion['scale'])) {
                            $aQuestion['varlabel'] = "[{$aQuestion['scale']}] ".$aQuestion['varlabel'];
            }
            if (isset($aQuestion['subquestion'])) {
                            $aQuestion['varlabel'] = "[{$aQuestion['subquestion']}] ".$aQuestion['varlabel'];
            }
            if (isset($aQuestion['subquestion2'])) {
                            $aQuestion['varlabel'] = "[{$aQuestion['subquestion2']}] ".$aQuestion['varlabel'];
            }
            if (isset($aQuestion['subquestion1'])) {
                            $aQuestion['varlabel'] = "[{$aQuestion['subquestion1']}] ".$aQuestion['varlabel'];
            }

            //write varlabel back to fieldmap
            $aFieldmap['questions'][$sSGQAkey]['varlabel'] = $aQuestion['varlabel'];

            //create value labels for question types with "fixed" answers (YES/NO etc.)
            if ((isset($aQuestion['other']) && $aQuestion['other'] == 'Y') || substr($aQuestion['fieldname'], -7) == 'comment') {
                $aFieldmap['questions'][$sSGQAkey]['commentother'] = true; //comment/other fields: create flag, so value labels are not attached (in close())
            } else {
                $aFieldmap['questions'][$sSGQAkey]['commentother'] = false;


                if ($aQuestion['type'] == 'M') {
                    $aFieldmap['answers'][$aQuestion['qid']]['0'][$yvalue] = array(
                        'code' => $yvalue,
                        'answer' => gT('Yes')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['0'] = array(
                        'code' => 0,
                        'answer' => gT('Not Selected')
                    );
                } elseif ($aQuestion['type'] == "P") {
                    $aFieldmap['answers'][$aQuestion['qid']]['0'][$yvalue] = array(
                        'code' => $yvalue,
                        'answer' => gT('Yes')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['0'] = array(
                        'code' => 0,
                        'answer' => gT('Not Selected')
                    );
                } elseif ($aQuestion['type'] == "G") {
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['0'] = array(
                        'code' => 'F',
                        'answer' => gT('Female')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['1'] = array(
                        'code' => 'M',
                        'answer' => gT('Male')
                    );
                } elseif ($aQuestion['type'] == "Y") {
                    $aFieldmap['answers'][$aQuestion['qid']]['0'][$yvalue] = array(
                        'code' => $yvalue,
                        'answer' => gT('Yes')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0'][$nvalue] = array(
                        'code' => $nvalue,
                        'answer' => gT('No')
                    );
                } elseif ($aQuestion['type'] == "C") {
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['1'] = array(
                        'code' => 1,
                        'answer' => gT('Yes')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['0'] = array(
                        'code' => 2,
                        'answer' => gT('No')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['9'] = array(
                        'code' => 3,
                        'answer' => gT('Uncertain')
                    );
                } elseif ($aQuestion['type'] == "E") {
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['1'] = array(
                        'code' => 1,
                        'answer' => gT('Increase')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['0'] = array(
                        'code' => 2,
                        'answer' => gT('Same')
                    );
                    $aFieldmap['answers'][$aQuestion['qid']]['0']['-1'] = array(
                        'code' => 3,
                        'answer' => gT('Decrease')
                    );
                }
            } // close: no-other/comment variable
        $aFieldmap['questions'][$sSGQAkey]['varname'] = $aQuestion['varname']; //write changes back to array
        } // close foreach question


        // clean up fieldmap (remove HTML tags, CR/LS, etc.)
        $aFieldmap = $this->stripArray($aFieldmap);
        return $aFieldmap;
    }


    /*  return a STATA-compatible variable name
     *    strips some special characters and fixes variable names starting with a number
     */
    protected function SPSSvarname($sVarname)
    {
        if (!preg_match("/^([a-z]|[A-Z])+.*$/", $sVarname)) {
//var starting with a number?
            $sVarname = "v".$sVarname; //add a leading 'v'
        }
        $sVarname = str_replace(array(
            "-",
            ":",
            ";",
            "!",
            "[",
            "]",
            " "
        ), array(
            "_",
            "_dd_",
            "_dc_",
            "_excl_",
            "_",
            "",
            "_"
        ), $sVarname);
        return $sVarname;
    }


    /*  strip html tags, blanks and other stuff from array, flattens text
     */
    protected function stripArray($tobestripped)
    {
        Yii::app()->loadHelper('export');
        function clean(&$item)
        {
            if (is_string($item)){
            $item = trim((htmlspecialchars_decode(stripTagsFull($item))));
            }

        }
        array_walk_recursive($tobestripped, 'clean');
        return ($tobestripped);
    }


    /* Function is called for every response
     * Here we just use it to create arrays with variable names and data
     */
    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        // function is called for every response to be exported....only write header once
        if (empty($this->headers)) {
            $this->headers = $headers;
            foreach ($this->headers as $iKey => &$sVarname) {
                $this->headers[$iKey] = $this->SPSSvarname($sVarname);
            }
        }
        // gradually fill response array...
        $this->customResponsemap[] = $values;
    }

    /*
    This function updates the fieldmap and recodes responses
    so output to XML in close() is a piece of cake...
    */
    protected function updateCustomresponsemap()
    {
        //go through each particpants' responses
        foreach ($this->customResponsemap as $iRespId => &$aResponses) {
            // go through variables and response items
            foreach ($aResponses as $iVarid => &$response) {
                $response = trim($response);
                //recode answercode=answer if codes are non-numeric (cannot be used with value labels)
                if ($this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['nonnumericanswercodes'] == true
                    && $this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['commentother'] == false) {
                    // set $iScaleID to the scale_id of the respective question, if it exists...if not set to '0'
                    $iScaleID = 0;
                    if (isset($this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['scale'])) {
                        $iScaleID = $this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['scale_id'];
                    }
                    $iQID = $this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['qid'];
                    if (isset($this->customFieldmap['answers'][$iQID][$iScaleID][$response]['answer'])) {
                        $response = trim($this->customFieldmap['answers'][$iQID][$iScaleID][$response]['answer']); // get answertext instead of answercode
                    }
                }
                
                
                if ($response != '') {
                    // recode some values from letters to numeric, so we can attach value labels and have more time doing statistics
                    switch ($this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['type']) {
                        case "G": //GENDER drop-down list
                            $response = str_replace(array(
                                'F',
                                'M'
                            ), array(
                                '0',
                                '1'
                            ), $response);
                            break;
                        case "Y": //YES/NO radio-buttons
                        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $response = str_replace(array(
                                'Y',
                                'N',
                                'U'
                            ), array(
                                '1',
                                '0',
                                '9'
                            ), $response);
                            break;
                        case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                            $response = str_replace(array(
                                'I',
                                'S',
                                'D'
                            ), array(
                                '1',
                                '0',
                                '-1'
                            ), $response);
                            break;
                        case "D": //replace in customResponsemap: date/time as string with STATA-timestamp
                            $response = strtotime($response.' GMT') * 1000 + 315619200000; // convert seconds since 1970 (UNIX) to milliseconds since 1960 (STATA)
                            break;
                        case "L":
                            // For radio lists, user wants code, not label
                            // TODO: We could skip this loop if we had answer code
                            //foreach ($this->customFieldmap['answers'][$iQID][$iScaleID] as $answer) {
                           //     if ($answer['answer'] == $response) {
                           //         $response = $answer['code'];
                           //         break;
                           //     }
                           // }
                            break;
                    }
                    
                    /* look at each of the responses and determine STATA data type and format of the respective variables
                       datatypes coded as follows:
                       1=""
                       2=byte
                       3=int
                       4=long
                       5=float
                       6=double
                       7=string
                    */
                    $numberresponse = trim($response);
                    if ($this->customFieldmap['info']['surveyls_numberformat'] == 1) {
// if settings: decimal seperator==','
                        $numberresponse = str_replace(',', '.', $response); // replace comma with dot so STATA can use float variables
                    }

                    if (is_numeric($numberresponse)) {
// deal with numeric responses/variables
                        if (ctype_digit($numberresponse)) {
// if it contains only digits (no dot) --> non-float number
                            if ($numberresponse >= $this->minByte && $numberresponse <= $this->maxByte) {
                                $iDatatype = 2; //this response is of STATA type 'byte'
                            } elseif ($numberresponse >= $this->minInt && $numberresponse <= $this->maxInt) {
                                $iDatatype = 3; // and this is is 'int'
                            } else {
                                if ($this->customFieldmap['questions'][$this->headersSGQA[$iVarid]]['type'] == 'D') {
// if datefield then a 'double' data type is needed
                                    $iDatatype = 6; // double
                                } else {
                                    $iDatatype = 4; //long
                                }
                            }
                        } else {
//non-integer numeric response
                            $iDatatype = 5; // float
                            $response = $numberresponse; //replace in customResponsemap: value with '.' as decimal
                        }
                    } else {
// non-numeric response
                        $iDatatype = 7; //string
                        $iStringlength = strlen($response); //for strings we need the length for the format and the data type
                    }
                } else {
                    $iDatatype = 1; // response = "" 
                }
                
                // initialize format and type (default: empty)
                if (!isset($aStatatypelist[$this->headersSGQA[$iVarid]]['type'])) {
                                    $aStatatypelist[$this->headersSGQA[$iVarid]]['type'] = 1;
                }
                if (!isset($aStatatypelist[$this->headersSGQA[$iVarid]]['format'])) {
                                    $aStatatypelist[$this->headersSGQA[$iVarid]]['format'] = 0;
                }
                
                // Does the variable need a higher datatype because of the current response?
                if ($aStatatypelist[$this->headersSGQA[$iVarid]]['type'] < $iDatatype) {
                                    $aStatatypelist[$this->headersSGQA[$iVarid]]['type'] = $iDatatype;
                }
                
                // if datatype is a string, set needed stringlength
                if ($iDatatype == 7) {
                    // Does the variable need a higher stringlength because of the current response?
                    if ($aStatatypelist[$this->headersSGQA[$iVarid]]['format'] < $iStringlength) {
                                            $aStatatypelist[$this->headersSGQA[$iVarid]]['format'] = $iStringlength;
                    }
                    
                }
                //write the recoded response back to the response array
                $this->customResponsemap[$iRespId][$iVarid] = $response;
            }
        }
        
        // translate coding into SPSS datatypes, format and length
        foreach ($aStatatypelist as $variable => $data) {
            switch ($data['type']) {
                case 7: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'str'.min($data['format'], $this->maxStringLength);
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%'.min($data['format'], $this->maxStringLength).'s';
                    break;
                case 6: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'double';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%tc';
                    break;
                case 5: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'float';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%10.0g';
                    break;
                case 4: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'long';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%10.0g';
                    break;
                case 3: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'int';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%10.0g';
                    break;
                case 2: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'byte';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%10.0g';
                    break;
                case 1: 
                    $this->customFieldmap['questions'][$variable]['statatype']   = 'byte';
                    $this->customFieldmap['questions'][$variable]['stataformat'] = '%9.0g';
                    break;
            }
        }
    }

    /* Utilizes customFieldmap[], customResponsemap[], headers[] and xmlwriter()
     * to output STATA-xml code in the following order
     * - headers
     * - descriptors: data types, list of variables, sorting variable, variable formatting, list of value labels, variable label
     * - data
     * - value labels
     */
    public function close()
    {

        $this->updateCustomresponsemap();


		include_once(dirname(__FILE__) . "/helpers/spss/vendor/autoload.php");

		$variables = array();

        foreach ($this->customFieldmap['questions'] as $question) {
			$tmpvar = array();
			$tmpvar['name'] = $question['varname'];		
            $tmpvar['format'] = Variable::FORMAT_TYPE_A;
            $tmpvar['width'] = 255; //$question[];		
//            $tmpvar['decimals'] = $question[];		
            $tmpvar['label'] = $question['varlabel'];		
//            $tmpvar['values'] = $question[];		
            $tmpvar['columns'] = 8;
            $tmpvar['alignment'] = Variable::ALIGN_LEFT;
            $tmpvar['measure'] = Variable::MEASURE_NOMINAL;
            $variables[] = $tmpvar;
        }

		$header = array(
            'prodName' => '@(#) IBM SPSS STATISTICS 64-bit Macintosh 23.0.0.0',
            'creationDate' => date('d M y'),
            'creationTime' => date('H:M:s'),
            'weightIndex' => 0);


		$writer = new \SPSS\Sav\Writer(['header' => $header, 'variables' => $variables]);

        foreach ($this->customResponsemap as $aResponses) {
			$tmpdat = array();
            foreach ($aResponses as $iVarid => $response) {
				$tmpdat[] = $response;
            }
			$writer->writeCase($tmpdat);
        }


        $writer->save("/tmp/test.sav");
		$writer->close();

		echo(file_get_contents("/tmp/test.sav"));

        fclose($this->handle);
    }
}
