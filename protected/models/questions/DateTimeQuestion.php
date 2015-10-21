<?php
namespace ls\models\questions;


use ls\interfaces\ResponseInterface;

class DateTimeQuestion extends TextQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        // Minimum date allowed in date question
        if (isset($this->date_min) && trim($this->date_min) != '') {
            // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
            if (trim($this->date_min) != '') {
                if ((strlen($this->date_min) == 4) && ($this->date_min >= 1900) && ($this->date_min <= 2099)) {
                    // backward compatibility: if only a year is given, add month and day
                    $date_min = '\'' . $this->date_min . '-01-01' . ' 00:00\'';
                } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $this->date_min)) {
                    $date_min = '\'' . $this->date_min . ' 00:00\'';
                } elseif (array_key_exists($this->date_min, $this->qcode2sgqa))  // refers to another question
                {
                    $date_min = $this->date_min . '.NAOK';
                }
            }
            $expression = '(is_empty(' . $this->varName . '.NAOK) || (' . $this->varName . '.NAOK >= date("Y-m-d H:i", strtotime(' . $date_min . ')) ))';
            $result[$expression] = gT("Date is too small.");
        }
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'date';
        return $result;
    }

    /**
     * Returns the date format for this question or, if not set, for the survey.
     */
    protected function getDateFormat() {
        if (isset($this->date_format)) {
            $aDateFormatDetails = [];
            $aDateFormatDetails['dateformat'] = $this->date_format;
            $aDateFormatDetails['phpdate'] = getPHPDateFromDateFormat($aDateFormatDetails['dateformat']);
            $aDateFormatDetails['jsdate'] = getJSDateFromDateFormat($aDateFormatDetails['dateformat']);
        }
        else
        {
            $aDateFormatDetails = \ls\helpers\SurveyTranslator::getDateFormatData(getDateFormatForSID($this->sid));
        }
        return $aDateFormatDetails;
    }
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param ResponseInterface $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(ResponseInterface $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        $result->setHtml(\TbHtml::dateField($this->sgqa, $response->{$this->sgqa}, [
            'data-validation-expression' => $this->getExpressionManager($response)->getJavascript(implode(' and ', array_keys($this->getValidationExpressions())))
        ]));
        return $result;
    }


}