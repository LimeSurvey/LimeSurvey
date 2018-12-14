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
class RenderListComment extends QuestionBaseRenderer
{
    public $sCoreClass = "ls-answers ";
    
    public $checkconditionFunction = "checkconditions";
    protected $maxoptionsize          = 35;
        
    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setAnsweroptions(null, $this->aQuestionAttributes['alphasort']==1);
    }

    public function getMainView()
    {
        return '/survey/questions/answer/list_with_comment';
    }

    public function renderList($sCoreClasses){
        $sRows = '';

        foreach ($this->aAnswerOptions[0] as $ansrow) {
            $itemData = array(
                'li_classes'             => 'answer-item radio-item',
                'name'                   => $this->sSGQA,
                'id'                     => 'answer'.$this->sSGQA.$ansrow['code'],
                'value'                  => $ansrow['code'],
                'check_ans'              => ($this->mSessionValue == $ansrow['code'] ? CHECKED :''),
                'checkconditionFunction' => $this->checkconditionFunction.'(this.value, this.name, this.type);',
                'labeltext'              => $ansrow->answerL10ns[$this->sLanguage]->answer,
            );
            $sRows .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/list/rows/answer_row', $itemData, true);
        }

        if ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {
            $itemData = array(
                'li_classes'=>'answer-item radio-item noanswer-item',
                'name'=>$this->sSGQA,
                'id'=>'answer'.$this->sSGQA,
                'value'=>'',
                'check_ans'=> ( $this->mSessionValue == '' || $this->mSessionValue == ' ') ? CHECKED :'',
                'checkconditionFunction'=>$this->checkconditionFunction.'(this.value, this.name, this.type)',
                'labeltext'=>gT('No answer'),
            );

            $sRows .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/list/rows/answer_row', $itemData, true);
        }

        $fname2 = $this->sSGQA.'comment';
        $tarows = ($this->getAnswerCount() > 8) ? $this->getAnswerCount() / 1.2 : 4;

        $this->sCoreClass .= " ".$sCoreClasses;

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/list/answer', array(
            'sRows'             => $sRows,
            'id'                => 'answer'.$this->sSGQA.'comment',
            'basename'          => $this->sSGQA,
            'coreClass'         => $this->sCoreClass,
            'hint_comment'      => gT('Please enter your comment here'),
            'name'              => $this->sSGQA.'comment',
            'tarows'            => floor($tarows),
            'has_comment_saved' => isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2],
            'comment_saved'     => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]),
            'java_name'         => 'java'.$this->sSGQA,
            'java_id'           => 'java'.$this->sSGQA,
            'java_value'        => $this->mSessionValue
            ), true);


        $inputnames[] = $this->sSGQA;
        $inputnames[] = $this->sSGQA.'comment';

        return array($answer, $inputnames);
    }

    public function renderDropdown($sCoreClasses){

        $sOptions = '';
        foreach ($this->aAnswerOptions[0] as $ansrow) {
            $itemData = array(
                'value' => $ansrow['code'],
                'check_ans' => ($this->mSessionValue == $ansrow['code'] ? SELECTED : ''),
                'option_text' => $ansrow->answerL10ns[$this->sLanguage]->answer,
            );
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/dropdown/rows/option', $itemData, true);

            if (strlen($ansrow->answerL10ns[$this->sLanguage]->answer) > $this->maxoptionsize) {
                $this->maxoptionsize = strlen($ansrow->answerL10ns[$this->sLanguage]->answer);
            }
        }

        if ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {
            $itemData = array(
                'classes' => ' noanswer-item ',
                'value' => '',
                'check_ans' => ($this->mSessionValue == '' ? SELECTED : ''),
                'option_text' => gT('No answer'),
            );
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/dropdown/rows/option', $itemData, true);
        }

        $fname2 = $this->sSGQA.'comment';
        $tarows =  ($this->getAnswerCount() > 8 ? ($this->getAnswerCount() / 1.2) : 4);      
        $tarows =  ($tarows > 15) ? 15 : $tarows;

        $this->maxoptionsize = $this->maxoptionsize * 0.72;

        if ($this->maxoptionsize < 33) {$this->maxoptionsize = 33; }
        if ($this->maxoptionsize > 70) {$this->maxoptionsize = 70; }

        $this->sCoreClass .= " ".$sCoreClasses;

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/dropdown/answer', array(
            'sOptions'               => $sOptions,
            'name'                   => $this->sSGQA,
            'coreClass'              => $this->sCoreClass,
            'id'                     => 'answer'.$this->sSGQA,
            'basename'               => $this->sSGQA,
            'show_noanswer'          => is_null($this->mSessionValue),
            'label_text'             => gT('Please enter your comment here'),
            'tarows'                 => $tarows,
            'maxoptionsize'          => $this->maxoptionsize,
            'comment_saved'          => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]),
            'value'                  => $this->mSessionValue,
            ), true);

        $inputnames[] = $this->sSGQA;
        $inputnames[] = $this->sSGQA.'comment';
        
        return array($answer, $inputnames);
    }

    public function getRows()
    {
        $sRows = "";
        return $sRows;
    }

   
    public function render($sCoreClasses = '')
    {
        if ($this->aQuestionAttributes['use_dropdown'] != 1) {
            return $this->renderList($sCoreClasses);
        }
        return $this->renderDropdown($sCoreClasses);
    }

}

/*
function do_listwithcomment($ia)
{
    //// Init variables

    // General variables
    global $thissurvey;
    $kpclass                = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $checkconditionFunction = "checkconditions";
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $maxoptionsize          = 35;
    $coreClass              = "ls-answers";
    $inputnames = [];

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]); // Question attribute variables
    $oQuestion           = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang)); // Getting question

    // Getting answers
    $ansresult    = $oQuestion->getOrderedAnswers($aQuestionAttributes['random_order'], $aQuestionAttributes['alphasort']);
    $anscount     = count($ansresult);
    $hint_comment = gT('Please enter your comment here');

    if ($aQuestionAttributes['use_dropdown'] != 1) {
        $sRows = '';
        $li_classes = 'answer-item radio-item';
        foreach ($ansresult as $ansrow) {
            $check_ans = '';

            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] == $ansrow['code']) {
                $check_ans = CHECKED;
            }

            $itemData = array(
                'li_classes'=>$li_classes,
                'name'                   => $this->sSGQA,
                'id'                     => 'answer'.$this->sSGQA.$ansrow['code'],
                'value'                  => $ansrow['code'],
                'check_ans'              => $check_ans,
                'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type);',
                'labeltext'              => $ansrow->answerL10ns[$sSurveyLang]->answer,
            );
            $sRows .= doRender('/survey/questions/answer/list_with_comment/list/rows/answer_row', $itemData, true);
        }

        // ==> rows
        $check_ans = '';
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            if ((!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] == ' ')) {
                $check_ans = CHECKED;
            } elseif (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] != '')) {
                $check_ans = '';
            }

            $itemData = array(
                'li_classes'=>$li_classes.' noanswer-item',
                'name'=>$this->sSGQA,
                'id'=>'answer'.$this->sSGQA,
                'value'=>'',
                'check_ans'=>$check_ans,
                'checkconditionFunction'=>$checkconditionFunction.'(this.value, this.name, this.type)',
                'labeltext'=>gT('No answer'),
            );

            $sRows .= doRender('/survey/questions/answer/list_with_comment/list/rows/answer_row', $itemData, true);
        }

        $fname2 = $this->sSGQA.'comment';
        $tarows = ($anscount > 8) ? $anscount / 1.2 : 4;


        $answer = doRender('/survey/questions/answer/list_with_comment/list/answer', array(
            'sRows'             => $sRows,
            'id'                => 'answer'.$this->sSGQA.'comment',
            'basename'          => $this->sSGQA,
            'coreClass'         => $coreClass,
            'hint_comment'      => $hint_comment,
            'kpclass'           => $kpclass,
            'name'              => $this->sSGQA.'comment',
            'tarows'            => floor($tarows),
            'has_comment_saved' => isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2],
            'comment_saved'     => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]),
            'java_name'         => 'java'.$this->sSGQA,
            'java_id'           => 'java'.$this->sSGQA,
            'java_value'        => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA]
            ), true);


        $inputnames[] = $this->sSGQA;
        $inputnames[] = $this->sSGQA.'comment';
    } else {
//Dropdown list
        $sOptions = '';
        foreach ($ansresult as $ansrow) {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA] == $ansrow['code']) {
                $check_ans = SELECTED;
            }

            $itemData = array(
                'value' => $ansrow['code'],
                'check_ans' => $check_ans,
                'option_text' => $ansrow['answer'],
            );
            $sOptions .= doRender('/survey/questions/answer/list_with_comment/dropdown/rows/option', $itemData, true);

            if (strlen($ansrow['answer']) > $maxoptionsize) {
                $maxoptionsize = strlen($ansrow['answer']);
            }
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1 && !is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA])) {
            $check_ans = "";
            if (trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA]) == '') {
                $check_ans = SELECTED;
            }
            $itemData = array(
                'classes' => ' noanswer-item ',
                'value' => '',
                'check_ans' => $check_ans,
                'option_text' => gT('No answer'),
            );
            $sOptions .= doRender('/survey/questions/answer/list_with_comment/dropdown/rows/option', $itemData, true);
        }
        $fname2 = $this->sSGQA.'comment';

        if ($anscount > 8) {
            $tarows = $anscount / 1.2;
        } else {
            $tarows = 4;
        }

        if ($tarows > 15) {
            $tarows = 15;
        }
        $maxoptionsize = $maxoptionsize * 0.72;

        if ($maxoptionsize < 33) {$maxoptionsize = 33; }
        if ($maxoptionsize > 70) {$maxoptionsize = 70; }


        $answer = doRender('/survey/questions/answer/list_with_comment/dropdown/answer', array(
            'sOptions'               => $sOptions,
            'name'                   => $this->sSGQA,
            'coreClass'              => $coreClass,
            'id'                     => 'answer'.$this->sSGQA,
            'basename'               => $this->sSGQA,
            'show_noanswer'          => is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA]),
            'label_text'             => $hint_comment,
            'kpclass'                => $kpclass,
            'tarows'                 => $tarows,
            'maxoptionsize'          => $maxoptionsize,
            'comment_saved'          => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]), /* htmlspecialchars(null)=="" right ? 
            'value'                  => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA],
            ), true);

        $inputnames[] = $this->sSGQA;
        $inputnames[] = $this->sSGQA.'comment';
    }
    return array($answer, $inputnames);
}*/