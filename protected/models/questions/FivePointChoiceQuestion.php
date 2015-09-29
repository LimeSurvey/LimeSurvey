<?php
namespace ls\models\questions;
use ls\interfaces\iAnswer;
use ls\interfaces\iResponse;

class FivePointChoiceQuestion extends FixedChoiceQuestion
{

    /**
     * @param null $scale
     * @return iAnswer[];
     */
    public function getAnswers($scale = null)
    {

        $answers = [];
        for ($i = 1; $i <= 5; $i++) {
            $answers[] = $answer = new \ls\components\QuestionAnswer($i, $i);
        }
        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) {
            $result[] = new \ls\components\QuestionAnswer("", gT("No answer"));
        }
        return $answers;
    }
    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     *
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'choice-5-pt-radio';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return string
     */
    public function render(iResponse$response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);

        $imageurl = App()->getConfig("imageurl");
        $checkconditionFunction = "checkconditions";

        $id = 'slider'.time().rand(0,100);
        $answer = "\n<ul id=\"{$id}\" class=\"answers-list radio-list\">\n";
        for ($fp=1; $fp<=5; $fp++)
        {
            $answer .= "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"$this->sgqa\" id=\"answer$this->sgqa$fp\" value=\"$fp\"";
            if ($response->{$this->sgqa} == $fp)
            {
                $answer .= 'checked="checked"';
            }
            $answer .= "/>\n<label for=\"answer$this->sgqa$fp\" class=\"answertext\">$fp</label>\n\t</li>\n";
        }
        if (!$this->bool_mandatory  && $this->survey->bool_shownoanswer) // Add "No ls\models\Answer" option if question is not mandatory
        {
            $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$this->sgqa\" id=\"answer".$this->sgqa."NANS\" value=\"\"";
            if (!$response->{$this->sgqa})
            {
                $answer .= 'checked="checked"';
            }
            $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer".$this->sgqa."NANS\" class=\"answertext\">".gT('No answer')."</label>\n\t</li>\n";

        }
        $answer .= "</ul>\n<input type=\"hidden\" name=\"java$this->sgqa\" id=\"java$this->sgqa\" value=\"".$response->{$this->sgqa}."\" />\n";
        $inputnames[]=$this->sgqa;

        if($this->slider_rating==1){
            App()->getClientScript()->registerCssFile(App()->getConfig('publicstyleurl') . 'star-rating.css');
            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts')."star-rating.js");
            $answer .= "<script type='text/javascript'>\n"
                . "  <!--\n"
                ." doRatingStar({$this->primaryKey});\n"
                ." -->\n"
                ."</script>\n";
        }

        if($this->slider_rating==2){
            App()->getClientScript()->registerCssFile(App()->getConfig('publicstyleurl') . 'slider-rating.css');
            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts')."slider-rating.js");
            $answer .= "<script type='text/javascript'>\n"
                . " <!--\n"
                ." doRatingSlider({$this->primaryKey});\n"
                ." -->\n"
                ."</script>\n";
        }
        $result->setHtml($answer);
        return $result;
    }



}