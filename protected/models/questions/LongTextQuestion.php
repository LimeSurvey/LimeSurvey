<?php
namespace ls\models\questions;

use ls\interfaces\iResponse;

class LongTextQuestion extends TextQuestion
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param Response $response
     * @param \ls\components\SurveySession $session
     * @return string
     */
    public function render(iResponse $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);

        $classes = ['question', 'answer-item', 'text-item'];
        if ($this->maximum_chars > 0 )
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maxlength= "maxlength='{$this->maximum_chars}' ";
            $classes[] = "maxchars";
            $classes[] = "maxchars-" . $this->maximum_chars;
        }

        $rows = isset($this->display_rows) ? $this->display_rows : 5;

        if (isset($this->text_input_width)) {
            $classes[] = "inputwidth-" . $this->text_input_width;
            $width = $this->text_input_width;
        } else {
            $width = 40;
        }

//        vdd($this->getValidationExpressions());
        $inputOptions =  [
            'class' => 'textarea',
            'rows' => $rows,
            'cols' => $width,
            'maxlength' => isset($this->maximum_chars) ? $this->maximum_chars : null,
            'id' => "answer{$this->sgqa}",
            'data-validation-expression' => $this->getExpressionManager($response)->getJavascript(implode(' and ', array_keys($this->getValidationExpressions())))
        ];

        $html = \TbHtml::tag('p', ['class' => implode(' ', $classes)],
            \TbHtml::label(gT('Your answer'), "answer{$this->sgqa}")
            . \TbHtml::textArea($this->sgqa, $response->{$this->sgqa}, $inputOptions)
        );

        $result->setHtml($html);



        return $result ;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'text-long';
        return $result;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        return [$this->sgqa => "text"];
    }


}