<?php
namespace ls\models\questions;


class RankingQuestion extends \ls\models\Question
{
    public function getAnswerScales()
    {
        return 1;
    }

    /**
     * Returns the number of scales for subquestions.
     * @return int Range: {0, 1, 2}
     */
    public function getSubQuestionScales()
    {
        return 1;
    }


    /**
     * @return iSubQuestion[]
     */
    public function getSubQuestions($scale = null) {
        $result = [];
        for ($i = 1; $i <= count($this->answers); $i++) {
            $result[] = new \ls\components\SubQuestion("{$this->title}$i", $i);
        }
        return $result;
    }

    public function getColumns()
    {
        $result = [];

        for ($i = 1; $i <= count($this->answers); $i++) {
            $result[$this->sgqa . $i] = "string(5)";
        }

        return $result;
    }

    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        $code = "that.{$this->title}";
        // @todo Add more validation here.
        $expr = "unique($code)";
        $result[$expr] = 'All must be ranked.';
        return $result;
    }

    /**
     * Returns the fields for this question.
     * @return QuestionResponseField[]
     */
    public function getFields()
    {
        $result = [];
        for ($i = 1; $i <= count($this->answers); $i++) {
            $result[] = $field = new \ls\components\QuestionResponseField($this->sgqa . $i, "{$this->title}_{$i}", $this);
            $field->setRelevanceScript($this->getRelevanceScript());
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
        $result[] = 'ranking';
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
        $imageurl = App()->getConfig("imageurl");

        $checkconditionFunction = "checkconditions";

        $answers = $this->getAnswers();

        $anscount= count($answers);

        $max_answers = is_numeric($this->max_answers) ? intval($this->max_answers) : $anscount;
        $min_answers = is_numeric($this->min_answers) ? intval($this->min_answers) : $anscount;

        // Get the max number of line needed
        $iMaxLine = $max_answers < $anscount ? $max_answers : $anscount;


        $html = '';
        // First start by a ranking without javascript : just a list of select box
        // construction select box
        $html .= '<div class="ranking-answers">
    <ul class="answers-list select-list">';
        for ($i=1; $i<=$iMaxLine; $i++)
        {
            $myfname=$this->sgqa.$i;
            $html .= "\n<li class=\"select-item\">";
            $html .="<label for=\"answer{$myfname}\">";
            if($i==1){
                $html .=gT('First choice');
            }else{
                $html .=sprintf(gT('Choice of rank %s'),$i);
            }
            $html .= "</label>";
            $html .= "<select name=\"{$myfname}\" id=\"answer{$myfname}\">\n";
            if (empty(App()->surveySessionManager->current->response->{$this->sgqa})) {
                $html .= "\t<option value=\"\"".'selected="selected"'.">".gT('Please choose...')."</option>\n";
            }
            foreach ($answers as $answer)
            {
                $thisvalue="";
                $html .="\t<option value=\"{$answer->code}\"";
                if (isset($_SESSION['survey_'.$this->sid][$myfname]) && $_SESSION['survey_'.$this->sid][$myfname] == $answer->code)
                {
                    $html .= 'selected="selected"';
                    $thisvalue=$_SESSION['survey_'.$this->sid][$myfname];
                }
                $html .=">".flattenText($answer->answer)."</option>\n";
            }
            $html .="</select>";
            // Hidden form: maybe can be replaced with ranking.js
            $html .="<input type=\"hidden\" id=\"java{$myfname}\" disabled=\"disabled\" value=\"{$thisvalue}\"/>";
            $html .="</li>";
            $inputnames[]=$myfname;
        }
        $html .="</ul>"
            . "<div style='display:none' id='ranking-{$this->primaryKey}-maxans'>{".$max_answers."}</div>"
            . "<div style='display:none' id='ranking-{$this->primaryKey}-minans'>{".$min_answers."}</div>"
            . "<div style='display:none' id='ranking-{$this->primaryKey}-name'>".$this->sgqa."</div>"
            . "</div>";
        // The list with HTML answers
        $html .="<div style=\"display:none\">";
        foreach ($answers as $answer)
        {
            $html.="<div id=\"htmlblock-{$this->primaryKey}-{$answer->code}\">{$answer->answer}</div>";
        }
        $html .="</div>";
        $cs = App()->getClientScript();
        $cs->registerPackage('jquery-actual'); // Needed to with jq1.9 ?
        $cs->registerScriptFile(App()->getPublicUrl() . "/scripts/ranking.js");
        $cs->registerCssFile(App()->getPublicUrl() . "/styles-public/ranking.css");

        if(!empty($this->choice_title))
        {
            $choice_title = htmlspecialchars($this->choice_title, ENT_QUOTES);
        }
        else
        {
            $choice_title = gT("Your Choices",'js');
        }
        if(!empty($this->rank_title))
        {
            $rank_title = htmlspecialchars($this->rank_title, ENT_QUOTES);
        }
        else
        {
            $rank_title=gT("Your Ranking",'js');
        }
        // hide_tip is managed by css with EM
        $rank_help = gT("Double-click or drag-and-drop items in the left list to move them to the right - your highest ranking item should be on the top right, moving through to your lowest ranking item.",'js');

        $html .= "<script type='text/javascript'>\n"
            . "  <!--\n"
            . "var aRankingTranslations = {
             choicetitle: '{$choice_title}',
             ranktitle: '{$rank_title}',
             rankhelp: '{$rank_help}'
            };\n"
            ." doDragDropRank({$this->primaryKey},{$this->showpopups},{$this->samechoiceheight},{$this->samelistheight});\n"
            ." -->\n"
            ."</script>\n";
        $result->setHtml($html);
        return $result;
    }

}


