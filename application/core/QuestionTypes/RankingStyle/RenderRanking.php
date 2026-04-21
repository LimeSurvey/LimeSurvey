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
class RenderRanking extends QuestionBaseRenderer
{
    private $inputnames = [];
    private $aDisplayAnswers = [];

    private $iMaxSubquestions;
    private $mMaxAnswers;
    private $mMinAnswers;
    private $sLabeltext;

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();  // Get ranking items as subquestions

        $maxSubquestionsAttribute = (int) $this->getQuestionAttribute('max_subquestions');
        $this->iMaxSubquestions = $maxSubquestionsAttribute > 0
            ? $maxSubquestionsAttribute
            : $this->getQuestionCount();

        $maxAnswersAttribute = trim((string) $this->getQuestionAttribute('max_answers'));
        $this->mMaxAnswers = $maxAnswersAttribute != ''
            ? (
                ($this->iMaxSubquestions < $this->getQuestionCount())
                ? min($this->iMaxSubquestions, (int) $maxAnswersAttribute)
                : (int) $maxAnswersAttribute
              )
            : $this->iMaxSubquestions;

        $this->mMinAnswers = $this->setDefaultIfEmpty($this->getQuestionAttribute('min_answers'), 0);
    }

    public function getMainView()
    {
        return '/survey/questions/answer/ranking';
    }

    public function getRows()
    {
        // Get the max number of line needed
        $iMaxLine = (
            (ctype_digit((string) $this->mMaxAnswers) && intval($this->mMaxAnswers) < $this->iMaxSubquestions)
                ? $this->mMaxAnswers
                : $this->iMaxSubquestions
        );
        $iMaxLine = min($iMaxLine, $this->getQuestionCount());

        $subQuestions = $this->aSubQuestions[0];

        // Prepare display subquestions for ranking interface
        $this->aDisplayAnswers = [];
        foreach ($subQuestions as $oSubQuestion) {
            $this->aDisplayAnswers[$oSubQuestion->qid] = array(
                'title' => (string)$oSubQuestion->title,
                //The renderer still believes it is an answer
                'answer' => $oSubQuestion->questionl10ns[$this->sLanguage]->question,
                'sqid' => $oSubQuestion->qid
            );
        }

        // Sort subquestions by question_order if available
        usort($subQuestions, function ($a, $b) {
            return ($a->question_order ?? 0) <=> ($b->question_order ?? 0);
        });

        $sSelects = '';
        $inputnames = [];

        // Iterate through subquestions instead of answer options
        for ($i = 1; $i <= $iMaxLine; $i++) {
            $oSubQuestion = $subQuestions[$i - 1];
            $myfname = $this->sSGQA . '_S' . $oSubQuestion->qid;
            $this->sLabeltext = $oSubQuestion->questionl10ns[$this->sLanguage]->question;
            $aItemData = [];

            $mSessionValue = $this->setDefaultIfEmpty($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname], false);

            if (!$mSessionValue) {
                $aItemData[] = array(
                    'value'      => '',
                    'selected'   => 'SELECTED',
                    'classes'    => '',
                    'id'         => '',
                    'optiontext' => gT('Please choose...'),
                );
            }

            // Show ranking positions (1, 2, 3, ...)
            foreach ($subQuestions as $oSubQuestion) {
                if ($mSessionValue == $oSubQuestion->title) {
                    $selected = SELECTED;
                } else {
                    $selected = '';
                }

                $aItemData[] = array(
                    'value' => $oSubQuestion->title,
                    'selected' => $selected,
                    'classes' => '',
                    'optiontext' => sprintf(gT('Rank %s'), $oSubQuestion->questionl10ns[$this->sLanguage]->question)
                );
            }

            $sSelects .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/answer_row',
                array(
                    'myfname' => $myfname,
                    'labeltext' => $this->sLabeltext,
                    'options' => $aItemData,
                    'thisvalue' => $mSessionValue ?? '',
                ),
                true
            );

            $inputnames[] = $myfname;
        }

        return $sSelects;
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';

        $sCoreClasses = "ls-answers answers-lists select-sortable-lists " . $sCoreClasses;
        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }

        $rankingTranslation = 'LSvar.lang.rankhelp="' . gT("Double-click or drag-and-drop items in the left list to move them to the right - your highest ranking item should be on the top right, moving through to your lowest ranking item.", 'js') . '";';
        $rankingTranslation .= 'LSvar.lang.rankadvancedhelp="' . gT("Drag or double-click images into order.", 'js') . '";';
        $this->addScript("rankingTranslation", $rankingTranslation, CClientScript::POS_BEGIN);
        //$this->applyScripts();

        if (trim((string) $this->getQuestionAttribute('choice_title', App()->language)) != '') {
            $choice_title = htmlspecialchars(trim((string) $this->getQuestionAttribute('choice_title', App()->language)), ENT_QUOTES);
        } else {
            $choice_title = gT("Available items", 'html');
        }

        if (trim((string) $this->getQuestionAttribute('rank_title', App()->language)) != '') {
            $rank_title = htmlspecialchars(trim((string) $this->getQuestionAttribute('rank_title', App()->language)), ENT_QUOTES);
        } else {
            $rank_title = gT("Your ranking", 'html');
        }
        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'coreClass'         => $sCoreClasses,
            'sSelects'          => $this->getRows(),
            'thisvalue'         => $this->mSessionValue,
            'answers'           => $this->aDisplayAnswers,
            'myfname'           => $this->sSGQA,
            'labeltext'         => $this->sLabeltext,
            'qId'               => $this->oQuestion->qid,
            'rankingName'       => $this->sSGQA,
            'basename'          => $this->sSGQA,
            'max_answers'       => $this->mMaxAnswers,
            'min_answers'       => $this->mMinAnswers,
            'choice_title'      => $choice_title,
            'rank_title'        => $rank_title,
            'showpopups'        => $this->getQuestionAttribute("showpopups"),
            'samechoiceheight'  => $this->getQuestionAttribute("samechoiceheight"),
            'samelistheight'    => $this->getQuestionAttribute("samelistheight"),
        ), true);

        $this->registerAssets();
        $inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }
}
