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

    public function createFieldmap($type=null)
    {
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $abrow)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}";
            $field['fieldname']=$fieldname;
            $field['type']=$type;
            $field['sid']=$this->surveyid;
            $field['gid']=$this->gid;
            $field['qid']=$this->id;
            $field['aid']=$abrow['title'];
            $field['sqid']=$abrow['qid'];
            $field['title']=$this->title;
            $field['question']=$this->text;
            $field['subquestion']=$abrow['question'];
            $field['group_name']=$this->groupname;
            $field['mandatory']=$this->mandatory;
            $field['hasconditions']=$this->conditionsexist;
            $field['usedinconditions']=$this->usedinconditions;
            $field['questionSeq']=$this->questioncount;
            $field['groupSeq']=$this->groupcount;
            $field['preg']=$this->preg;
            $q = clone $this;
            if(isset($this->defaults) && isset($this->defaults[$abrow['qid']])) $q->default=$field['defaultvalue']=$this->defaults[$abrow['qid']];
            $q->fieldname = $fieldname;
            $q->aid=$field['aid'];
            $q->question=$abrow['question'];
            $q->sq=$abrow['question'];
            $q->sqid=$abrow['qid'];
            $field['q']=$q;
            $map[$fieldname]=$field;
        }
        return $map;
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
}
?>