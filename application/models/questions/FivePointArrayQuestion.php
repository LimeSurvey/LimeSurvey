<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:56 AM
 */

namespace ls\models\questions;


class FivePointArrayQuestion extends FixedArrayQuestion
{

    public function getAnswers($scale = null)
    {
        // TODO: Implement getAnswers() method.
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'array-5-pt';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(\Response $response, \SurveySession $session)
    {
        $result = parent::render($response, $session);
        $classes = [
            'question',
            'subquestion-list',
            'questions-list'
        ];

        $caption=gT("An array with sub-question on each line. The answers are value from 1 to 5 and are contained in the table header. ");

        if (is_numeric($this->answer_width)) {
            $width = $this->answer_width;
            $classes[] ="answerwidth-".trim($this->answer_width);
        } else {
            $width = 20;
        }
        $cellWidth  = 5; // number of columns

        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) //Question is not mandatory
        {
            ++$cellWidth; // add another column
        }
        $cellWidth = round((( 100 - $width ) / $cellWidth) , 1); // convert number of columns to percentage of table width

        $rightQuestions = array_filter($this->subQuestions, function(\Question $this) {
            return strpos($this->question, '|') !== false;
        });

        if (count($rightQuestions) >0) {
            $width = $width/2;
        } else {
        }


        $html = \TbHtml::openTag('table', [
            'class' => implode(' ', $classes),
            'summary' => $caption
        ]);
        $html .= "\t<colgroup class=\"col-responses\">\n"
            . "\t<col class=\"col-answers\" width=\"$width%\" />\n";
        $odd_even = '';

        for ($xc=1; $xc<=5; $xc++)
        {
            $odd_even = alternation($odd_even);
            $html .= "<col class=\"$odd_even\" width=\"$cellWidth%\" />\n";
        }
        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $html .= "<col class=\"col-no-answer $odd_even\" width=\"$cellWidth%\" />\n";
        }
        $html .= "\t</colgroup>\n\n"
            . "\t<thead>\n<tr class=\"array1 dontread\">\n"
            . "\t<td>&nbsp;</td>\n";
        for ($xc=1; $xc<=5; $xc++)
        {
            $html .= "\t<th>$xc</th>\n";
        }
        if (count($rightQuestions) > 0) {$html .= "\t<td width='$width%'>&nbsp;</td>\n";}
        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) //Question is not mandatory
        {
            $html .= \TbHtml::tag('th', [], gT('No answer'));
        }
        $html .= "</tr></thead>\n";

        $tableContent = '<tbody>';
        $n=0;

        foreach ($this->subQuestions as $subQuestion)
        {
            $relevance = strtr($this->getFilterExpression(), ['{VALUE}' => $subQuestion->question]);
            $tableContent .= $this->renderSubQuestion($subQuestion, $response, $relevance, count($rightQuestions) > 0);
        }

        $html .= $tableContent . "\n</tbody>\t</table>\n";
        $result->setHtml($html);
        return $result;
    }

    protected function renderSubQuestion(\Question $subQuestion, \Response $response, $relevance, $right_exists) {
        $result = [];
        $em = $this->getExpressionManager($response);
        $result[] = \TbHtml::openTag('tr', [
            'data-relevance-expression' => $em->getJavascript($relevance),
            'data-enabled-expression' => $this->array_filter_style == 1 ? $em->getJavascript($relevance) : null,
            'class' => !$em->ProcessBooleanExpression($relevance) ? 'irrelevant' : ''
        ]);
        $trbc = '';
        $fieldName = $this->sgqa . $subQuestion->title;

        $parts = explode('|', $subQuestion->question, 2);
        $answerText = $parts[0];

        /* Check if this item has not been answered */
        if ($this->bool_mandatory)
        {
            $answerText = "<span class=\"errormandatory\">{$answerText}</span>";
        }

        $trbc = alternation($trbc , 'row');

        // Get array_filter stuff
        $result[] = \TbHtml::tag('th', ['class' => 'answertext'], $answerText);
        for ($i=1; $i<=5; $i++)
        {
            $result[] = "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n"
                ."\n\t<input class=\"radio\" type=\"radio\" name=\"$fieldName\" id=\"answer$fieldName-$i\" value=\"$i\"";
            if ($response->$fieldName == $i)
            {
                $result[] = CHECKED;
            }
            $result[] = " />"
                . "<label class=\"hide read\" for=\"answer$fieldName-$i\">{$i}</label>\n"
                . "\n</td>\n";
        }


        if (count($parts) > 1) {
            $result[] = "\t<td class=\"answertextright\" style='text-align:left;' >{$parts[1]}</td>\n";
        } elseif ($right_exists) {
            $result[] = "\t<td class=\"answertextright\" style='text-align:left;' >&nbsp;</td>\n";
        }


        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer)
        {
            $result[] = "\t<td class=\"answer-item radio-item noanswer-item\">\n"
                ."\n\t<input class=\"radio\" type=\"radio\" name=\"$fieldName\" id=\"answer$fieldName-\" value=\"\" ";
            if (empty($response->$fieldName)) {
                $result[] = CHECKED;
            }
            $result[] = " />\n"
                ."<label class=\"hide read\" for=\"answer$fieldName-\">".gT('No answer')."</label>"
                ."</td>\n";
        }

        $result[] = \TbHtml::closeTag('tr');
//        vdd($result);
        return implode("\n", $result);
    }



}