<?php

/**
 * RenderClass for Boilerplate Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderMultipleChoice extends QuestionBaseRenderer
{
    private $sCoreClasses = 'ls-answers checkbox-list answers-list';
    private $inputnames = [];

    private $iColumnWidth;
    private $iMaxRowsByColumn;
    private $iNbCols;



    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();

        $this->iNbCols = $this->setDefaultIfEmpty($this->getQuestionAttribute('display_columns'), 1);

        $this->iColumnWidth = round(12 / $this->iNbCols);
        $this->iColumnWidth = ($this->iColumnWidth >= 1) ? $this->iColumnWidth : 1;
        $this->iColumnWidth = ($this->iColumnWidth <= 12) ? $this->iColumnWidth : 12;
        $this->iMaxRowsByColumn = ceil($this->getQuestionCount() / $this->iNbCols);
    
        if ($this->iNbCols > 1) {
            $this->sCoreClasses .= " multiple-list nbcol-{$this->iNbCols}";
        }
    }

    public function getMainView()
    {
        return '/survey/questions/answer/multiplechoice';
    }
    
    public function getRows()
    {
        $aRows = [];

        if($this->getQuestionCount() == 0) {
            return $aRows;
        }

        $checkconditionFunction = "checkconditions"; 
        /// Generate answer rows
        foreach ($this->aSubQuestions[0] as $oQuestion) {

            $myfname = $this->sSGQA.$oQuestion->title;
            $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname], '');
            $this->inputnames[] = $myfname;

            ////
            // Insert row
            // Display the answer row
            $aRows[] = array(
                'myfname'                 => $myfname,
                'name'                    => $this->sSGQA, // field name
                'title'                   => $oQuestion->title,
                'question'                => $oQuestion->questionL10ns[$this->sLanguage]->question,
                'ansrow'                  => array_merge($oQuestion->attributes, $oQuestion->questionL10ns[$this->sLanguage]->attributes),
                'checkedState'            => ($mSessionValue == 'Y' ? CHECKED : ''),
                'sCheckconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
                'sValue'                  => $mSessionValue,
                'relevanceClass'          => $this->getCurrentRelevecanceClass($myfname)
            );
        }

        if ($this->oQuestion->other == 'Y') {
          $aRows[] = $this->getOtherRow();
        }

        return $aRows;
    }

    public function getOtherRow(){

        $sSeparator = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];
        $oth_checkconditionFunction = ($this->getQuestionAttribute('other_numbers_only') == 1) ? "fixnum_checkconditions" : "checkconditions";

        $myfname = $this->sSGQA.'other';
        $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname], '');
        $this->inputnames[] = $myfname;

        $sValue = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValue .= htmlspecialchars($dispVal, ENT_QUOTES);
        }

        // TODO : check if $sValueHidden === $sValue
        $sValueHidden = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValueHidden = htmlspecialchars($dispVal, ENT_QUOTES);
        }

        ////
        // Insert row
        // Display the answer row
        return array(
            'myfname'                    => $myfname,
            'othertext'                  => $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:')),
            'sValue'                     => $sValue,
            'oth_checkconditionFunction' => $oth_checkconditionFunction,
            'checkconditionFunction'     => "checkconditions",
            'sValueHidden'               => $sValueHidden,
            'checkedState'               => ($mSessionValue != '' ? CHECKED : ''),
            'relevanceClass'             => $this->getCurrentRelevecanceClass($myfname),
            'other'                      => true
        );
    }


    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];
        $this->sCoreClasses .= " ".$sCoreClasses;

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/answer', array(
            'aRows'            => $this->getRows(),
            'name'             => $this->sSGQA,
            'basename'         => $this->sSGQA,
            'anscount'         => $this->getQuestionCount(),
            'iColumnWidth'     => $this->iColumnWidth,
            'iMaxRowsByColumn' => $this->iMaxRowsByColumn,
            'iNbCols'          => $this->iNbCols,
            'coreClass'        => $this->sCoreClasses,
        ), true);

        $this->inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }

    protected function getQuestionCount($iScaleId=0){
        if(!empty($this->aSubQuestions)) {

            $counter = count($this->aSubQuestions[$iScaleId]);
            if($this->oQuestion->other == 'Y') {
                $counter++;
            }
            return $counter;
        }
        return 0;
    }
}


/*
function do_multiplechoice($ia)
{
    //// Init variables

    // General variables
    global $thissurvey;
    $kpclass                = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $inputnames             = array(); // It is used!
    $checkconditionFunction = "checkconditions"; // name of the function to check condition TODO : check is used more than once
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $coreClass = "ls-answers checkbox-list answers-list";
    // Question attribute variables
    $aQuestionAttributes    = (array) QuestionAttribute::model()->getQuestionAttributes($ia[0]); // Question attributes
    $othertext              = (trim($aQuestionAttributes['other_replace_text'][$sSurveyLang]) != '') ? $aQuestionAttributes['other_replace_text'][$sSurveyLang] : gT('Other:'); // text for 'other'
    $iNbCols                = (trim($aQuestionAttributes['display_columns']) != '') ? $aQuestionAttributes['display_columns'] : 1; // number of columns
    $aSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator             = $aSeparator['separator'];

    $oth_checkconditionFunction = ($aQuestionAttributes['other_numbers_only'] == 1) ? "fixnum_checkconditions" : "checkconditions";

    //// Retrieving datas

    // Getting question
    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang));
    $other     = $oQuestion->other;

    // Getting answers
    $aQuestions = $oQuestion->getOrderedSubQuestions($aQuestionAttributes['random_order'], $aQuestionAttributes['exclude_all_others']);
    $anscount  = count($aQuestions);
    $anscount  = ($other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!

    // First we calculate the width of each column
    // Max number of column is 12 http://getbootstrap.com/css/#grid
    $iColumnWidth = round(12 / $iNbCols);
    $iColumnWidth = ($iColumnWidth >= 1) ? $iColumnWidth : 1;
    $iColumnWidth = ($iColumnWidth <= 12) ? $iColumnWidth : 12;
    $iMaxRowsByColumn = ceil($anscount / $iNbCols);

    if ($iNbCols > 1) {
        $coreClass .= " multiple-list nbcol-{$iNbCols}";
    }

    /// Generate answer rows
    foreach ($aQuestions as $aQuestion) {
        $myfname = $ia[1].$aQuestion['title'];

        $relevanceClass = currentRelevecanceClass($iSurveyId, $ia[1], $myfname, $aQuestionAttributes);
        $checkedState = '';
        /* If the question has already been ticked, check the checkbox
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y') {
                $checkedState = 'CHECKED';
            }
        }

        $sCheckconditionFunction = $checkconditionFunction.'(this.value, this.name, this.type)';
        $sValue                  = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
        $inputnames[]            = $myfname;


        ////
        // Insert row
        // Display the answer row
        $aRows[] = array(
            'name'                    => $ia[1], // field name
            'title'                   => $aQuestion['title'],
            'question'                => $aQuestion->questionL10ns[$sSurveyLang]->question,
            'ansrow'                  => $aQuestion,
            'checkedState'            => $checkedState,
            'sCheckconditionFunction' => $sCheckconditionFunction,
            'myfname'                 => $myfname,
            'sValue'                  => $sValue,
            'relevanceClass'          => $relevanceClass,
            );

    }

    //==>  rows
    if ($other == 'Y') {
        $myfname = $ia[1].'other';
        $relevanceClass = currentRelevecanceClass($iSurveyId, $ia[1], $myfname, $aQuestionAttributes);
        $checkedState = '';
        // othercbox can be not display, because only input text goes to database
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) != '') {
            $checkedState = 'CHECKED';
        }

        $sValue = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValue .= htmlspecialchars($dispVal, ENT_QUOTES);
        }

        // TODO : check if $sValueHidden === $sValue
        $sValueHidden = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValueHidden = htmlspecialchars($dispVal, ENT_QUOTES); ;
        }

        $inputnames[] = $myfname;
        ++$anscount;

        ////
        // Insert row
        // Display the answer row
        $aRows[] = array(
            'myfname'                    => $myfname,
            'othertext'                  => $othertext,
            'checkedState'               => $checkedState,
            'kpclass'                    => $kpclass,
            'sValue'                     => $sValue,
            'oth_checkconditionFunction' => $oth_checkconditionFunction,
            'checkconditionFunction'     => $checkconditionFunction,
            'sValueHidden'               => $sValueHidden,
            'relevanceClass'             => $relevanceClass,
            'other'                      => true
            );


    }



    // ==> answer
    $answer = doRender('/survey/questions/answer/multiplechoice/answer', array(
        'aRows'            => $aRows,
        'name'             => $ia[1],
        'basename'         => $ia[1],
        'anscount'         => $anscount,
        'iColumnWidth'     => $iColumnWidth,
        'iMaxRowsByColumn' => $iMaxRowsByColumn,
        'iNbCols'          => $iNbCols,
        'coreClass'        => $coreClass,
        ), true);

    return array($answer, $inputnames);
}
*/
