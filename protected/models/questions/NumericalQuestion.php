<?php
namespace ls\models\questions;

class NumericalQuestion extends \ls\models\Question {
    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'numeric';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\ResponseInterface $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        $extraclass ="";
        $answertypeclass = "numeric";

        if (trim($this->prefix[$session->language])!='') {
            $prefix=$this->prefix[$session->language];
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }
        if ($this->thousands_separator == 1) {
            App()->clientScript->registerPackage('jquery-price-format');
            App()->clientScript->registerScriptFile(App()->getConfig('generalscripts').'numerical_input.js');
            $extraclass .= " thousandsseparator";
        }
        if (trim($this->suffix[$session])!='') {
            $suffix=$this->suffix[$session];
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }
        if (intval(trim($this->maximum_chars))>0 && intval(trim($this->maximum_chars))<20)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($this->maximum_chars));
            $maxlength= " maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= " maxlength='20' ";
        }
        if (trim($this->text_input_width)!='')
        {
            $tiwidth=$this->text_input_width;
            $extraclass .=" inputwidth-".trim($this->text_input_width);
        }
        else
        {
            $tiwidth=10;
        }

        if (trim($this->num_value_int_only)==1) {
            $acomma="";
            $extraclass .=" integeronly";
            $answertypeclass .= " integeronly";
            $integeronly=1;
        } else {
            $acomma = \ls\helpers\SurveyTranslator::getRadixPointData($this->survey->getLocalizedNumberFormat())['separator'];
            $integeronly=0;
        }

        $fValue= App()->surveySessionManager->current->response->{$this->sgqa};
        // Fix the display value : Value is stored as decimal in SQL then return dot and 0 after dot. Seems only for numerical question type
        if(strpos($fValue,"."))
        {
            $fValue=rtrim(rtrim($fValue,"0"),".");
        }
        $fValue = str_replace('.',$acomma,$fValue);



        // --> START NEW FEATURE - SAVE
        $html = "<p class='question answer-item text-item numeric-item {$extraclass}'>"
            . " <label for='answer{$this->sgqa}' class='hide label'>".gT('Your answer')."</label>\n$prefix\t"
            . "<input class='text {$answertypeclass}' type=\"text\" size=\"$tiwidth\" name=\"$this->sgqa\"  title=\"".gT('Only numbers may be entered in this field.')."\" "
            . "id=\"answer{$this->sgqa}\" value=\"{$fValue}\""
            . " {$maxlength} />\t{$suffix}\n</p>\n";
        // --> END NEW FEATURE - SAVE

        $result->setHtml($html);

        return $result;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        // @todo Change this based on parameters?
        return [$this->sgqa => "decimal (30,10)"];
    }


}
