<?php
abstract class ArrayQuestion extends QuestionModule
{
    protected $children;
    public function getInputNames()
    {
        $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$this->id}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
        $lresult = dbExecuteAssoc($lquery);
        
        foreach ($this->getChildren() as $ansrow)
        {
            foreach ($lresult->readAll() as $lrow)
            {
                $inputnames[] = $this->fieldname.$ansrow['title'].'_'.$lrow['title'];
            }
        }
        return $inputnames;
    }
    
    protected function getChildren()
    {
        if ($this->children) return $this->children;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        return $this->children = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }
}
?>