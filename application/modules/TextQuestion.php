<?php
abstract class TextQuestion extends QuestionModule
{
    public function getPopup($notanswered=null)
    {
        $clang = Yii::app()->lang;

        if (is_array($notanswered) && Yii::app()->getConfig('showpopups')) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        {
            global $mandatorypopup, $popup;
            //POPUP WARNING
            if (!isset($mandatorypopup))
            {
                return $popup="<script type=\"text/javascript\">\n
                <!--\n $(document).ready(function(){
                alert(\"".$clang->gT("You cannot proceed until you enter some text for one or more questions.", "js")."\");});\n //-->\n
                </script>\n";
            }else
            {
                return $popup="<script type=\"text/javascript\">\n
                <!--\n $(document).ready(function(){
                alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.", "js")."\");});\n //-->\n
                </script>\n";
            }
        }
        return false;
    }

    public function generateQuestionInfo()
    {
        return array(
            'q' => $this,
            'qid' => $this->id,
            'qseq' => $this->questioncount,
            'gseq' => $this->groupcount,
            'sgqa' => $this->surveyid . 'X' . $this->gid . 'X' . $this->id,
            'mandatory'=>$this->mandatory,
            'varName' => $this->getVarName(),
            'fieldname' => $this->fieldname,
            'preg' => (isset($this->preg) && trim($this->preg) != '') ? $this->preg : NULL,
            'rootVarName' => $this->title,
            'subqs' => array()
            );
    }

    public function getPregSQ($sgqaNaming, $sq)
    {
        $sgqa = substr($sq['jsVarName'],4);
        if ($sgqaNaming)
        {
            return '(if(is_empty('.$sgqa.'.NAOK),0,!regexMatch("' . $this->preg . '", ' . $sgqa . '.NAOK)))';
        }
        else
        {
            return '(if(is_empty('.$sq['varName'].'.NAOK),0,!regexMatch("' . $this->preg . '", ' . $sq['varName'] . '.NAOK)))';
        }
    }

    public function generateSQInfo($ansArray)
    {
        return array(array(
            'varName' => $this->getVarName(),
            'rowdivid' => $this->surveyid . 'X' . $this->gid . 'X' . $this->id,
            'jsVarName' => 'java' . $this->surveyid . 'X' . $this->gid . 'X' . $this->id,
            'jsVarName_on' => $this->jsVarNameOn(),
            ));
    }

    public function getAdditionalValParts()
    {
        $valParts[] = "\n  if(isValidOther" . $this->id . "){\n";
        $valParts[] = "    $('#question" . $this->id . " :input').addClass('em_sq_validation').removeClass('error').addClass('good');\n";
        $valParts[] = "  }\n  else {\n";
        $valParts[] = "    $('#question" . $this->id . " :input').addClass('em_sq_validation').removeClass('good').addClass('error');\n";
        $valParts[] = "  }\n";
        return $valParts;
    }

    public function availableOptions()
    {
        return array('other' => false, 'valid' => true, 'mandatory' => true);
    }

    public function getTypeHelp($language)
    {
        return $language->gT("Please write your answer here:");
    }

    public function getVarAttributeLEM($sgqa,$value)
    {
        return htmlspecialchars(parent::getVarAttributeLEM($sgqa,$value),ENT_NOQUOTES);
    }

}
?>
