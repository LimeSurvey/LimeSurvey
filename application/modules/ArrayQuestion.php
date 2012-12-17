<?php
abstract class ArrayQuestion extends QuestionModule
{
    protected $children;

    protected function getChildren()
    {
        if ($this->children) return $this->children;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY question_order";
        }
        return $this->children = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }

    public function createFieldmap()
    {
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $abrow)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}";
            $q = clone $this;
            if(isset($this->defaults) && isset($this->defaults[$abrow['qid']])) $q->default=$this->defaults[$abrow['qid']];
            $q->fieldname = $fieldname;
            $q->aid=$abrow['title'];
            $q->question=$abrow['question'];
            $q->sq=$abrow['question'];
            $q->sqid=$abrow['qid'];
            $q->preg=$this->haspreg;
            $map[$fieldname]=$q;
        }
        return $map;
    }

    public function statisticsFieldmap()
    {
        return true;
    }

    public function jsVarNameOn()
    {
        return 'java'.$this->fieldname;
    }

    public function getCsuffix()
    {
        return $this->aid;
    }

    public function getSqsuffix()
    {
        return '_' . $this->aid;
    }

    public function getVarName()
    {
        return $this->title . '_' . $this->aid;
    }

    public function getQuestion()
    {
        return $this->sq;
    }

    public function getRowDivID()
    {
        return $this->fieldname;
    }

    public function getMandatoryTip()
    {
        $clang=Yii::app()->lang;
        return $clang->gT('Please complete all parts').'.';
    }

    public function compareField($sgqa, $sq)
    {
        return $sgqa == $sq['rowdivid'] || $sgqa == ($sq['rowdivid'] . 'comment');
    }

    public function includeRelevanceStatus()
    {
        return true;
    }
}
?>
