<?php
namespace ls\models\questions;
use ls\interfaces\iResponse;
use ls\interfaces\iSubQuestion;

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
        return 1;
    }



    abstract protected function getSummary();
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(iResponse$response, \ls\components\SurveySession $session)
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
        foreach($this->getAnswers() as $dummy) {
            $i++;
            $html .= \TbHtml::tag('col', [
                'class' => $i % 2 ? "odd" : "even",
                'width' => $cellWidth . "%"
            ]);
        }

        $html .= "\t</colgroup>\n\n"
            . "\t<thead>\n<tr class=\"array1 dontread\">\n"
            . "\t<td>&nbsp;</td>\n";

        foreach($this->getAnswers() as $answer) {
            $html .= \TbHtml::tag('th', ['data-code' => $answer->getCode()], $answer->getLabel());
        }
        $html .= "\t<td width='$width%'>&nbsp;</td>\n";
        $html .= "</tr></thead>\n";

        $tableContent = '<tbody>';
        $n=0;

        /** @var iSubQuestion $subQuestion */
        foreach ($this->subQuestions as $subQuestion)
        {
            // We explode to make sure we only get the left part.
            $relevance = strtr($this->getFilterExpression(), ['{VALUE}' => explode('|', $subQuestion->getLabel())[0]]);
            $tableContent .= $this->renderSubQuestion($subQuestion, $response, $relevance, count($rightQuestions) > 0);
        }

        $html .= $tableContent . "\n</tbody>\t</table>\n";
        $result->setHtml($html);
        return $result;
    }

    protected function renderSubQuestion(iSubQuestion $subQuestion, iResponse $response, $relevance) {
        bP();
        $result = [];
        $em = $this->getExpressionManager($response);
        $result[] = \TbHtml::openTag('tr', [
            'data-relevance-expression' => $em->getJavascript($relevance),
            'data-enabled-expression' => $this->array_filter_style == 1 ? $em->getJavascript($relevance) : null,
        ]);
        $fieldName = $this->sgqa . $subQuestion->getCode();

        $parts = explode('|', $subQuestion->getLabel(), 2);
        $answerText = $parts[0];

        // Get array_filter stuff
        $answers = $this->getAnswers();
//        if (!array_key_exists('{TEXTLEFT}', $answers)) {
//            $answers = ['{TEXTLEFT}' => true] + $answers;
//        }
//        if (!array_key_exists('{TEXTRIGHT}', $answers)) {
//            $answers = $answers + ['{TEXTRIGHT}' => true];
//        }

        bP('aloop');
        $result[] = \TbHtml::tag('th', ['class' => 'answertext'], $answerText);
        foreach($answers as $answer)
        {
            if ($answer->getCode() == "{TEXTLEFT}") {
                $result[] = \TbHtml::tag('th', ['class' => 'answertext'], $answerText);
            } elseif ($answer->getCode() == "{TEXTRIGHT}") {
                $result[] = \TbHtml::tag('th', ['class' => 'answertext'], isset($parts[1]) ? $parts[1] : '');
            } else {
                $result[] = "\t<td class=\"answer_cell_00{$answer->getCode()} answer-item radio-item\">\n"
                    . "\n\t<input class=\"radio\" type=\"radio\" name=\"$fieldName\" id=\"answer$fieldName-{$answer->getCode()}\" value=\"{$answer->getCode()}\"";
                if ($response->$fieldName == $answer->getCode()) {
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

    /**
     * Does this question support custom answers?
     * @return boolean
     */
    public function getHasCustomAnswers()
    {
        return false;
    }


}