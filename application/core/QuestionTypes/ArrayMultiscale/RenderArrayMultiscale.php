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
class RenderArrayMultiscale extends QuestionBaseRenderer
{
    protected $aLabels;
    protected $numrows;
    protected $aMandatoryViolationSubQ;
    protected $useDropdownLayout;
    protected $answertypeclass;
    protected $answerwidth;
    protected $defaultWidth;
    protected $doDualScaleFunction;
    protected $inputnames;

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect = false);
        // Set attributes
        if ($this->getQuestionAttribute('use_dropdown') == 1) {
            $this->useDropdownLayout = true;
            $this->sCoreClass .= " dropdown-array";
            $this->answertypeclass .= " dropdown";
            $this->doDualScaleFunction = "doDualScaleDropDown"; // javascript function to lauch at end of answers
        } else {
            $this->useDropdownLayout = false;
            $this->sCoreClass .= " radio-array";
            $this->answertypeclass .= " radio";
            $this->doDualScaleFunction = "doDualScaleRadio";
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $this->answerwidth = trim((string) $this->getQuestionAttribute('answer_width'));
            $this->defaultWidth = false;
        } else {
            $this->answerwidth = 33;
            $this->defaultWidth = true;
        }
    }

    public function getMainView()
    {
        return '/survey/questions/answer/arrays/dualscale';
    }

    public function getRows()
    {
        return;
    }

    public function prepareLabels()
    {
        $this->setAnsweroptions();
        foreach ($this->aAnswerOptions as $iScaleId => $aScale) {
            $this->aLabels[$iScaleId] = [];
            foreach ($aScale as $oAnswerOption) {
                $this->aLabels[$iScaleId][] = [
                    'code' => $oAnswerOption->code,
                    'title' => $oAnswerOption->answerl10ns[$this->sLanguage]->answer
                ];
            }
        }
    }

    public function parseLabelsToArray(&$aData)
    {
        $this->numrows = 0;
        foreach ($this->aAnswerOptions as $iScaleId => $aScale) {
            foreach ($aScale as $oAnswerOption) {
                $aData['labelans' . $iScaleId][$oAnswerOption->code] = $oAnswerOption->answerl10ns[$this->sLanguage]->answer;
                $aData['labelcode' . $iScaleId][$oAnswerOption->code] = $oAnswerOption->code;
            }
            if (isset($aData['labelans' . $iScaleId])) {
                $this->numrows = $this->numrows + count($aData['labelans' . $iScaleId]);
            }
        }
        return $aData;
    }

    public function getPositioningAndSizing(&$aData)
    {
        // Find if we have right and center text
        /* All of this part seem broken actually : we don't send it to view and don't explode it */
        $sQuery  = "SELECT count(question) FROM {{questions}} q JOIN {{question_l10ns}} l  ON l.qid=q.qid WHERE parent_qid=" . $this->oQuestion->qid . " and scale_id=0 AND question like '%|%'";
        $rigthCount  = Yii::app()->db->createCommand($sQuery)->queryScalar();
        // $right_exists: flag to find out if there are any right hand answer parts. leaving right column but don't force with
        $rightexists = ($rigthCount > 0);

        $sQuery  = "SELECT count(question) FROM {{questions}} q JOIN {{question_l10ns}} l  ON l.qid=q.qid WHERE parent_qid=" . $this->oQuestion->qid . " and scale_id=0 AND question like '%|%|%'";
        $centerCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
        // $center_exists: flag to find out if there are any center hand answer parts. leaving center column but don't force with
        $centerexists  = ($centerCount > 0);
        /* Then always set to false : see bug https://bugs.limesurvey.org/view.php?id=11750 */
        //~ $rightexists=false;
        //~ $centerexists=false;

        $leftheader  = $this->setDefaultIfEmpty($this->getQuestionAttribute('dualscale_headerA', $this->sLanguage), '');
        $rightheader = $this->setDefaultIfEmpty($this->getQuestionAttribute('dualscale_headerB', $this->sLanguage), '');

        $shownoanswer = ($this->oQuestion->mandatory != "Y" && SHOW_NO_ANSWER == 1);

        if ($shownoanswer) {
            $this->numrows++;
        }
        /* right and center come from answer => go to answer part*/
        $numColExtraAnswer = 0;
        $rightwidth = 0;
        $separatorwidth = 4;
        $columnswidth = 100 - $this->answerwidth;

        if ($rightexists) {
            $numColExtraAnswer++;
        } elseif ($shownoanswer) {
            $columnswidth -= 4;
            $rightwidth = 4;
        }
        if ($centerexists) {
            $numColExtraAnswer++;
        } else {
            $columnswidth -= 4;
        }
        if ($numColExtraAnswer > 0) {
            $extraanswerwidth = $this->answerwidth / $numColExtraAnswer; /* If there are 2 separator : set to 1/2 else to same */
            if ($this->defaultWidth) {
                $columnswidth -= $this->answerwidth;
            } else {
                $this->answerwidth  = $this->answerwidth / 2;
            }
        } else {
            $extraanswerwidth = $separatorwidth;
        }
        $cellwidth = $columnswidth / ($this->numrows ? $this->numrows : 1);

        // Header row and colgroups
        $aData['answerwidth'] = $this->answerwidth;
        $aData['cellwidth'] = $cellwidth;
        $aData['separatorwidth'] = $centerexists ? $extraanswerwidth : $separatorwidth;
        $aData['shownoanswer'] = $shownoanswer;
        $aData['rightexists'] = $rightexists;
        $aData['rightwidth'] = $rightexists ? $extraanswerwidth : $rightwidth;

        // build first row of header if needed
        $aData['leftheader'] = $leftheader;
        $aData['rightheader'] = $rightheader;
        $aData['rightclass'] = ($rightexists) ? " header_answer_text_right" : "";
    }

    public function parseSubquestionsDropdown(&$aData)
    {
        $anscount = count($this->aSubQuestions[0]);

        foreach ($this->aSubQuestions[0] as $i => $oQuestionRow) {
            $myfname = $this->sSGQA . $oQuestionRow->title;
            $myfname0 = $this->sSGQA . $oQuestionRow->title . "#0";
            $myfid0 = $this->sSGQA . $oQuestionRow->title . "_0";
            $myfname1 = $this->sSGQA . $oQuestionRow->title . "#1";
            $myfid1 = $this->sSGQA . $oQuestionRow->title . "_1";
            $sActualAnswer0 = $this->setDefaultIfEmpty($this->getFromSurveySession($myfname0), "");
            $sActualAnswer1 = $this->setDefaultIfEmpty($this->getFromSurveySession($myfname1), "");


            $answertext = $oQuestionRow->questionl10ns[$this->sLanguage]->question;

            $aData['aSubQuestions'][$i]['question'] = $answertext;
            $aData['aSubQuestions'][$i]['myfname'] = $myfname;
            $aData['aSubQuestions'][$i]['myfname0'] = $myfname0;
            $aData['aSubQuestions'][$i]['myfid0'] = $myfid0;
            $aData['aSubQuestions'][$i]['myfname1'] = $myfname1;
            $aData['aSubQuestions'][$i]['myfid1'] = $myfid1;
            $aData['aSubQuestions'][$i]['sActualAnswer0'] = $sActualAnswer0;
            $aData['aSubQuestions'][$i]['sActualAnswer1'] = $sActualAnswer1;
            $aData['aSubQuestions'][$i]['odd'] = ($i % 2);
            // Set mandatory alert
            $aData['aSubQuestions'][$i]['alert'] = (($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S') && (in_array($myfname0, $this->aMandatoryViolationSubQ) || in_array($myfname1, $this->aMandatoryViolationSubQ)));
            $aData['aSubQuestions'][$i]['mandatoryviolation'] = (($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S') && (in_array($myfname0, $this->aMandatoryViolationSubQ) || in_array($myfname1, $this->aMandatoryViolationSubQ)));
            // Array filter : maybe leave EM do the trick
            $aData['aSubQuestions'][$i]['sDisplayStyle'] = "";

            $aData['labels0'] = $this->aLabels[0];
            $aData['labels1'] = $this->aLabels[1];
            $aData['aSubQuestions'][$i]['showNoAnswer0'] = ($sActualAnswer0 != '' && ($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER);
            $aData['aSubQuestions'][$i]['showNoAnswer1'] = ($sActualAnswer1 != '' && ($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER);

            $this->inputnames[] = $myfname0;
            $this->inputnames[] = $myfname1;
        }
    }

    public function parseSubquestionsNoDropdown(&$aData)
    {
        $repeatheadings     = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings  = Yii::app()->getConfig("minrepeatheadings");
        $repeatheadings     = $this->setDefaultIfEmpty($this->getQuestionAttribute('repeat_headings'), $repeatheadings);
        $anscount = count($this->aSubQuestions[0]);

        //Only use the 0 scale
        $fn = 0;
        foreach ($this->aSubQuestions[0] as $i => $oQuestionRow) {
            // Build repeat headings if needed

            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    $aData['aSubQuestions'][$i]['repeatheadings'] = true;
                }
            } else {
                $aData['aSubQuestions'][$i]['repeatheadings'] = false;
            }

            $answertext = $oQuestionRow->questionl10ns[$this->sLanguage]->question;
            // right and center answertext: not explode for ? Why not
            if (strpos((string) $answertext, '|') !== false) {
                $answertextright = (string) substr((string) $answertext, strpos((string) $answertext, '|') + 1);
                $answertext = (string) substr((string) $answertext, 0, strpos((string) $answertext, '|'));
            } else {
                $answertextright = "";
            }
            if (strpos($answertextright, '|')) {
                $answertextcenter = (string) substr($answertextright, 0, strpos($answertextright, '|'));
                $answertextright = (string) substr($answertextright, strpos($answertextright, '|') + 1);
            } else {
                $answertextcenter = "";
            }

            $myfname = $this->sSGQA . $oQuestionRow->title;
            $myfname0 = $this->sSGQA . $oQuestionRow->title . '#0';
            $myfid0 = $this->sSGQA . $oQuestionRow->title . '_0';
            $myfname1 = $this->sSGQA . $oQuestionRow->title . '#1'; // new multi-scale-answer
            $myfid1 = $this->sSGQA . $oQuestionRow->title . '_1';

            $aData['aSubQuestions'][$i]['title'] = $oQuestionRow->title;
            $aData['aSubQuestions'][$i]['myfname'] = $myfname;
            $aData['aSubQuestions'][$i]['myfname0'] = $myfname0;
            $aData['aSubQuestions'][$i]['myfid0'] = $myfid0;
            $aData['aSubQuestions'][$i]['myfname1'] = $myfname1;
            $aData['aSubQuestions'][$i]['myfid1'] = $myfid1;

            $aData['aSubQuestions'][$i]['answertext'] = $answertext;
            $aData['aSubQuestions'][$i]['answertextcenter'] = $answertextcenter;
            $aData['aSubQuestions'][$i]['answertextright'] = $answertextright;

            $aData['aSubQuestions'][$i]['odd'] = ($i % 2);

            // Check the Sub Q mandatory violation
            if (($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S') && (in_array($myfname0, $this->aMandatoryViolationSubQ) || in_array($myfname1, $this->aMandatoryViolationSubQ))) {
                $aData['aSubQuestions'][$i]['showmandatoryviolation'] = true;
            } else {
                $aData['aSubQuestions'][$i]['showmandatoryviolation'] = false;
            }

            // Get array_filter stuff
            $aData['aSubQuestions'][$i]['sDisplayStyle'] = '';

            array_push($this->inputnames, $myfname0);

            if (!empty($this->getFromSurveySession($myfname0))) {
                $aData['aSubQuestions'][$i]['sessionfname0'] = $this->getFromSurveySession($myfname0);
            } else {
                $aData['aSubQuestions'][$i]['sessionfname0'] = '';
            }

            if (isset($aData['labelcode0'])) {
                foreach ($aData['labelcode0'] as $j => $ld) {
                    // First label set
                    if (!is_null($this->getFromSurveySession($myfname0)) && $this->getFromSurveySession($myfname0) == $ld) {
                        $aData['labelcode0_checked'][$oQuestionRow->title][$ld] = CHECKED;
                    } else {
                        $aData['labelcode0_checked'][$oQuestionRow->title][$ld] = "";
                    }
                }
            }

            if (isset($aData['labelans1'])) {
                if (count($aData['labelans1']) > 0) {
                // if second label set is used

                    if (!empty($this->getFromSurveySession($myfname1))) {
                        $aData['aSubQuestions'][$i]['sessionfname1'] = $this->getFromSurveySession($myfname1);
                    } else {
                        $aData['aSubQuestions'][$i]['sessionfname1'] = '';
                    }

                    if ($aData['shownoanswer']) {
                        // No answer for accessibility and no javascript (but hide hide even with no js: need reworking)
                        $fname0value = $this->getFromSurveySession($myfname0);
                        // If value is empty, notset should be checked.
                        // string "0" should be considered as valid answer,
                        // so notset should not be checked in that case.
                        if ($fname0value !== '0' && empty($fname0value)) {
                            $aData['aSubQuestions'][$i]['myfname0_notset'] = CHECKED;
                        } else {
                            $aData['aSubQuestions'][$i]['myfname0_notset'] = "";
                        }
                    }

                    array_push($this->inputnames, $myfname1);

                    foreach ($aData['labelcode1'] as $j => $ld) {
                        // second label set
                        if (!is_null($this->getFromSurveySession($myfname1)) && $this->getFromSurveySession($myfname1) == $ld) {
                            $aData['labelcode1_checked'][$oQuestionRow->title][$ld] = CHECKED;
                        } else {
                            $aData['labelcode1_checked'][$oQuestionRow->title][$ld] = "";
                        }
                    }
                }
            }

            $aData['answertextright'] = $answertextright;
            if ($aData['shownoanswer'] && isset($aData['labelans1'])) {
                if (count($aData['labelans1']) > 0) {
                    $fname1value = $this->getFromSurveySession($myfname1);
                    // If value is empty, notset should be checked.
                    // string "0" should be considered as valid answer,
                    // so notset should not be checked in that case.
                    if ($fname1value !== '0' && $this->isNoAnswerChecked($myfname1)) {
                        $aData['aSubQuestions'][$i]['myfname1_notset'] = CHECKED;
                    } else {
                        $aData['aSubQuestions'][$i]['myfname1_notset'] = "";
                    }
                } else {
                    $fname0value = $this->getFromSurveySession($myfname0);
                    // If value is empty, notset should be checked.
                    // string "0" should be considered as valid answer,
                    // so notset should not be checked in that case.
                    if ($fname0value !== '0' && empty($fname0value)) {
                        //$answer .= CHECKED;
                        $aData['aSubQuestions'][$i]['myfname0_notset'] = CHECKED;
                    } else {
                        $aData['aSubQuestions'][$i]['myfname0_notset'] = '';
                    }
                }
            }
            $fn++;
        }
    }

    public function renderDropdown()
    {
        $aData = [];
        $aData['coreClass'] = $this->sCoreClass;
        $aData['basename'] = $this->sSGQA;

        // Get attributes for Headers and Prefix/Suffix
        if (trim((string) $this->getQuestionAttribute('dropdown_prepostfix', $this->sLanguage)) != '') {
            list($ddprefix, $ddsuffix) = explode("|", (string) $this->getQuestionAttribute('dropdown_prepostfix', $this->sLanguage));
        } else {
            $ddprefix = null;
            $ddsuffix = null;
        }

        $aData['ddprefix'] = $ddprefix;
        $aData['ddsuffix'] = $ddsuffix;

        if (trim((string) $this->getQuestionAttribute('dropdown_separators')) != '') {
            $aSeparator = explode('|', (string) $this->getQuestionAttribute('dropdown_separators'));
            if (isset($aSeparator[1])) {
                $interddSep = $aSeparator[1];
            } else {
                $interddSep = $aSeparator[0];
            }
        } else {
            $interddSep = '';
        }

        if ($interddSep) {
            $separatorwidth = 8;
        } else {
            $separatorwidth = 4;
        }
        $this->setAnsweroptions();
        $this->setSubquestions();

        $this->getPositioningAndSizing($aData);

        $aData['interddSep'] = $interddSep;
        $aData['separatorwidth'] = $separatorwidth;

        $this->prepareLabels();
        $this->parseSubquestionsDropdown($aData);

        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/answer_dropdown',
            $aData,
            true
        );
        return $answer;
    }

    public function renderNoDropdown()
    {
        $aData = [];
        $aData['coreClass'] = $this->sCoreClass;
        $aData['basename'] = $this->sSGQA;
        $aData['answertypeclass'] = $this->answertypeclass;


        $this->setAnsweroptions();
        $this->setSubquestions();

        $this->parseLabelsToArray($aData);
        $this->getPositioningAndSizing($aData);
        $this->parseSubquestionsNoDropdown($aData);

        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/answer',
            $aData,
            true
        );
        return $answer;
    }

    public function render($sCoreClasses = '')
    {
        $this->inputnames = [];
        $this->sCoreClass  = "ls-answers subquestion-list questions-list";

        $aLastMoveResult   = LimeExpressionManager::GetLastMoveResult();
        $this->aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && ($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S'))
                                        ? explode("|", (string) $aLastMoveResult['unansweredSQs'])
                                        : [];

        if ($this->useDropdownLayout === false) {
            $answer = $this->renderNoDropdown();
        } else {
            $answer =  $this->renderDropdown();
        }

        $this->registerAssets();
        $this->inputnames[] = $this->sSGQA;

        if (!Yii::app()->getClientScript()->isScriptFileRegistered(Yii::app()->getConfig('generalscripts') . "dualscale.js", LSYii_ClientScript::POS_BEGIN)) {
            Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "dualscale.js", LSYii_ClientScript::POS_BEGIN);
        }
        Yii::app()->getClientScript()->registerScript('doDualScaleFunction' . $this->oQuestion->qid, "{$this->doDualScaleFunction}({$this->oQuestion->qid});", LSYii_ClientScript::POS_POSTSCRIPT);

        return array($answer, $this->inputnames);
    }
}
