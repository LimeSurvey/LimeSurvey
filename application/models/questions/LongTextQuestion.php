<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/21/15
 * Time: 10:15 AM
 */

namespace ls\models\questions;


use ls\interfaces\iRenderable;

class LongTextQuestion extends TextQuestion implements iRenderable
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param Response $response
     * @param \SurveySession $session
     * @return string
     */
    public function render(\Response $response, \SurveySession $session)
    {
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

        $html = \TbHtml::tag('p', ['class' => implode(' ', $classes)],
            \TbHtml::label(gT('Your answer'), "answer{$this->sgqa}")
            . \TbHtml::textArea($this->sgqa, $response->{$this->sgqa}, [
                'class' => 'textarea',
                'rows' => $rows,
                'cols' => $width,
                'maxlength' => isset($this->maximum_chars) ? $this->maximum_chars : 0,
                'id' => "answer{$this->sgqa}"

            ])
        );

        return $html;
    }
}