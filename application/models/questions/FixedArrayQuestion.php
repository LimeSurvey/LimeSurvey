<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:47 AM
 */

namespace ls\models\questions;
use ls\interfaces\iResponse;

/**
 * Class FixedArrayQuestion
 * Base class for array questions that have fixed answers.
 * @package ls\models\questions
 */
abstract class FixedArrayQuestion extends BaseArrayQuestion
{


    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 0;
    }



    abstract protected function getSummary();
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(iResponse$response, \SurveySession $session)
    {
        $result = parent::render($response, $session);
        $classes = [
            'question',
            'subquestion-list',
            'questions-list'
        ];


        if (is_numeric($this->answer_width)) {
            $width = $this->answer_width;
            $classes[] ="answerwidth-".trim($this->answer_width);
        } else {
            $width = 20;
        }

        $cellWidth = round((( 100 - $width ) / count($this->getAnswers())) , 1); // convert number of columns to percentage of table width

        $rightQuestions = array_filter($this->subQuestions, function(\Question $this) {
            return strpos($this->question, '|') !== false;
        });


        $html = \TbHtml::openTag('table', [
            'class' => implode(' ', $classes),
            'summary' => $this->getSummary()
        ]);
        $html .= "\t<colgroup class=\"col-responses\">\n"
            . "\t<col class=\"col-answers\" width=\"$width%\" />\n";

        $i = 0;
        foreach($this->getAnswers() as $value => $answer) {
            $i++;
            $html .= \TbHtml::tag('col', [
                'class' => $i % 2 ? "odd" : "even",
                'width' => $cellWidth . "%"
            ]);
        }

        $html .= "\t</colgroup>\n\n"
            . "\t<thead>\n<tr class=\"array1 dontread\">\n"
            . "\t<td>&nbsp;</td>\n";

        foreach($this->getAnswers() as $value => $answer) {
            $html .= \TbHtml::tag('th', [], $answer);
        }
        $html .= "\t<td width='$width%'>&nbsp;</td>\n";
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

    protected function renderSubQuestion(\Question $subQuestion, \Response $response, $relevance) {
        bP();
        $result = [];
        $em = $this->getExpressionManager($response);
        $result[] = \TbHtml::openTag('tr', [
            'data-relevance-expression' => $em->getJavascript($relevance),
            'data-enabled-expression' => $this->array_filter_style == 1 ? $em->getJavascript($relevance) : null,
        ]);
        $fieldName = $this->sgqa . $subQuestion->title;

        $parts = explode('|', $subQuestion->question, 2);
        $answerText = $parts[0];

        // Get array_filter stuff
        $answers = $this->getAnswers();
        if (!array_key_exists('{TEXTLEFT}', $answers)) {
            $answers = ['{TEXTLEFT}' => true] + $answers;
        }
        if (!array_key_exists('{TEXTRIGHT}', $answers)) {
            $answers = $answers + ['{TEXTRIGHT}' => true];
        }

        bP('aloop');
        foreach($answers as $value => $answer)
        {
            if ($value == "{TEXTLEFT}") {
                $result[] = \TbHtml::tag('th', ['class' => 'answertext'], $answerText);
            } elseif ($value == "{TEXTRIGHT}") {
                $result[] = \TbHtml::tag('th', ['class' => 'answertext'], isset($parts[1]) ? $parts[1] : '');
            } else {
                $result[] = "\t<td class=\"answer_cell_00$value answer-item radio-item\">\n"
                    . "\n\t<input class=\"radio\" type=\"radio\" name=\"$fieldName\" id=\"answer$fieldName-$value\" value=\"$value\"";
                if ($response->$fieldName == $value) {
                    $result[] = 'checked="checked"';
                }
                $result[] = " /></td>\n";
            }
        }
        eP('aloop');


        $result[] = \TbHtml::closeTag('tr');
        eP();
        return implode("\n", $result);
    }

}