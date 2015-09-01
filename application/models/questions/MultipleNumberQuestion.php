<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:19 AM
 */

namespace ls\models\questions;


class MultipleNumberQuestion extends MultipleTextQuestion
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['numeric-multi'];
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \SurveySession $session)
    {
        $result = parent::render($response, $session);
        $html = \TbHtml::openTag('ul');
        foreach($this->subQuestions as $subQuestion) {
            $fieldName = $this->sgqa . $subQuestion->title;
            $html .= \TbHtml::openTag('li');
            $html .= \TbHtml::numberFieldControlGroup($fieldName, $response->$fieldName, [
                'label' => $subQuestion->question
            ]);
            $html .= \TbHtml::closeTag('li');
        }
        $html .= \TbHtml::closeTag('ul');
        $result->setHtml($html);
        return $result;

        //------------------------------------------------------------------------------------------------------------//
        //------------------------------------------------------------------------------------------------------------//
        // ORIGINAL IMPLEMENTATION BELOW
        //------------------------------------------------------------------------------------------------------------//
        //------------------------------------------------------------------------------------------------------------//
        $extraclass ="";
        $checkconditionFunction = "fixnum_checkconditions";

        $html='';
        $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($this->survey->getLocalizedNumberFormat())['separator'];

        //Must turn on the "numbers only javascript"
        $extraclass .=" numberonly";
        if ($this->thousands_separator == 1) {
            App()->clientScript->registerPackage('jquery-price-format');
            App()->clientScript->registerScriptFile(App()->getConfig('generalscripts').'numerical_input.js');
            $extraclass .= " thousandsseparator";
        }

        if (intval(trim($this->maximum_chars))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($this->maximum_chars));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= " maxlength='25' ";
        }

        if (trim($this->prefix)!='') {
            $prefix=$this->prefix;
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }

        if (trim($this->suffix)!='') {
            $suffix=$this->suffix;
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }



        if (trim($this->text_input_width)!='')
        {
            $tiwidth=$this->text_input_width;
            $extraclass .=" inputwidth".trim($this->text_input_width);
        }
        else
        {
            $tiwidth=10;
        }
        $prefixclass="numeric";

        if ($this->slider_layout==1)
        {

            $prefixclass="slider";
            $slider_layout=true;
            $extraclass .=" withslider";
            $slider_step=trim(LimeExpressionManager::ProcessString("{{$this->slider_accuracy}}", $this->primaryKey,
                array(), 1, 1));
            $slider_step =  (is_numeric($slider_step))?$slider_step:1;
            $slider_min = trim(LimeExpressionManager::ProcessString("{{$this->slider_min}}", $this->primaryKey,
                array(), 1, 1));
            $slider_mintext = $slider_min =  (is_numeric($slider_min))?$slider_min:0;
            $slider_max = trim(LimeExpressionManager::ProcessString("{{$this->slider_max}}", $this->primaryKey,
                array(), 1, 1));
            $slider_maxtext = $slider_max =  (is_numeric($slider_max))?$slider_max:100;
            $slider_default=trim(LimeExpressionManager::ProcessString("{{$this->slider_default}}",
                $this->primaryKey, array(), 1, 1));
            $slider_default =  (is_numeric($slider_default))?$slider_default:"";

            if ($slider_default == '' && $this->slider_middlestart==1)
            {
                $slider_middlestart = intval(($slider_max + $slider_min)/2);
            }
            else
            {
                $slider_middlestart = '';
            }

            $slider_separator= (trim($this->slider_separator)!='')?$this->slider_separator:"";
            $slider_reset=($this->slider_reset)?1:0;
        }
        else
        {
            $slider_layout = false;
        }

        $fn = 1;

        $answer_main = '';

        foreach($this->subQuestions as $subQuestion)
        {
            $myfname = $this->sgqa . $subQuestion->title;
            if ($subQuestion->question == "") {$subQuestion->question = "&nbsp;";}
            if ($slider_layout === false || $slider_separator == '')
            {
                $theanswer = $subQuestion->question;
                $sliderleft='';
                $sliderright='';
            }
            else
            {
                $aAnswer=explode($slider_separator,$subQuestion->question);
                $theanswer=(isset($aAnswer[0]))?$aAnswer[0]:"";
                $sliderleft=(isset($aAnswer[1]))?$aAnswer[1]:"";
                $sliderright=(isset($aAnswer[2]))?$aAnswer[2]:"";
                $sliderleft="<div class=\"slider_lefttext\">$sliderleft</div>";
                $sliderright="<div class=\"slider_righttext\">$sliderright</div>";
            }


            $answer_main .= "<label for=\"answer$myfname\" class=\"{$prefixclass}-label\">{$theanswer}</label>\n";

            $answer_main .= "{$sliderleft}<span class=\"input\">\n\t".$prefix."\n\t<input class=\"text\" type=\"number\" step=\"any\" size=\"".$tiwidth."\" name=\"".$myfname."\" id=\"answer".$myfname."\" title=\"".gT('Only numbers may be entered in this field.')."\" value=\"";
            if (isset($_SESSION['survey_'.App()->getConfig('surveyID')][$myfname]))
            {
                $dispVal = $_SESSION['survey_'.App()->getConfig('surveyID')][$myfname];
                if(strpos($dispVal,"."))
                {
                    $dispVal=rtrim(rtrim($dispVal,"0"),".");
                }
                $dispVal = str_replace('.',$sSeparator,$dispVal);
                $answer_main .= $dispVal;
            }

            $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '." {$maxlength} />\n\t".$suffix."\n</span>{$sliderright}\n\t</li>\n";

            $fn++;
            $inputnames[]=$myfname;
        }
        if (trim($this->equals_num_value) != ''
            || trim($this->min_num_value) != ''
            || trim($this->max_num_value) != ''
        )
        {
            $qinfo = LimeExpressionManager::GetQuestionStatus($this->primaryKey);
            if (trim($this->equals_num_value) != '')
            {
                $answer_main .= "\t<li class='multiplenumerichelp help-item'>\n"
                    . "<span class=\"label\">".gT('Remaining: ')."</span>\n"
                    . "<span id=\"remainingvalue_{$this->primaryKey}\" class=\"dynamic_remaining\">$prefix\n"
                    . "{" . $qinfo['sumRemainingEqn'] . "}\n"
                    . "$suffix</span>\n"
                    . "\t</li>\n";
            }

            $answer_main .= "\t<li class='multiplenumerichelp  help-item'>\n"
                . "<span class=\"label\">".gT('Total: ')."</span>\n"
                . "<span id=\"totalvalue_{$this->primaryKey}\" class=\"dynamic_sum\">$prefix\n"
                . "{" . $qinfo['sumEqn'] . "}\n"
                . "$suffix</span>\n"
                . "\t</li>\n";
        }
        $html .= "<ul class=\"subquestions-list questions-list text-list {$prefixclass}-list\">\n".$answer_main."</ul>\n";


        if($this->slider_layout==1)
        {
            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts')."numeric-slider.js");
            App()->getClientScript()->registerCssFile(App()->getConfig('publicstyleurl') . "numeric-slider.css");
            if ($slider_default != "")
            {
                $slider_startvalue = $slider_default;
                $slider_displaycallout=1;
            }
            elseif ($slider_middlestart != '')
            {
                $slider_startvalue = $slider_middlestart;
                $slider_displaycallout=0;
            }
            else
            {
                $slider_startvalue = 'NULL';
                $slider_displaycallout=0;
            }
            $slider_showminmax=($this->slider_showminmax==1)?1:0;
            //some var for slider
            $aJsLang=array(
                'reset' => gT('Reset'),
                'tip' => gT('Please click and drag the slider handles to enter your answer.'),
            );
            $aJsVar=array(
                'slider_showminmax'=>$slider_showminmax,
                'slider_min' => $slider_min,
                'slider_mintext'=>$slider_mintext,
                'slider_max' => $slider_max,
                'slider_maxtext'=>$slider_maxtext,
                'slider_step'=>$slider_step,
                'slider_startvalue'=>$slider_startvalue,
                'slider_displaycallout'=>$slider_displaycallout,
                'slider_prefix' => $prefix,
                'slider_suffix' => $suffix,
                'slider_reset' => $slider_reset,
                'lang'=> $aJsLang,
            );
            $html .= "<script type='text/javascript'><!--\n"
                . " doNumericSlider({$this->primaryKey},".ls_json_encode($aJsVar).");\n"
                . " //--></script>";
        }
        $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];

        return [$html, $inputnames];
        
    }


}
