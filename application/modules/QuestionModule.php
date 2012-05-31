<?php
abstract class QuestionModule
{
    public $id;
    public $fieldname;
    public $title;
    public $text;
    public $gid;
    public $mandatory;
    public $hasConditions; //boolean
    public $downstreamConditions;
    public $questionCount;
    private $attributes = false;

    abstract public function getAnswerHTML();
    
    public function getAttributeValues()
    {
        if ($this->attributes) return $this->attributes;
        if (!$this->id) return false;
        $row = Questions::model()->findByAttributes(array('qid' => $this->id)); //, 'parent_qid' => 0), array('group' => 'type')
        if (empty($row))
        {
            return false;
        }
        else
        {
            $row = $row->getAttributes();
        }
        $surveyid = $row['sid'];

        $aLanguages = array_merge((array)Survey::model()->findByPk($surveyid)->language, Survey::model()->findByPk($surveyid)->additionalLanguages);

        //Now read available attributes, make sure we do this only once per request to save
        //processing cycles and memory
        $attributes = questionAttributes();
        $available = $this->availableAttributes();

        $aResultAttributes = array();
        foreach($available as $attribute){
            $default = array_key_exists('default', $attributes[$attribute])?$attributes[$attribute]['default']:'';
            if (array_key_exists('i18n', $attributes[$attribute]) && $attributes[$attribute]['i18n'])
            {
                foreach ($aLanguages as $sLanguage)
                {
                    $aResultAttributes[$attribute][$sLanguage]=$default;
                }
            }
            else
            {
                $aResultAttributes[$attribute]=$default;
            }
        }

        $result = Question_attributes::model()->findAllByAttributes(array('qid' => $this->id));
        foreach ($result as $row)
        {
            $row = $row->attributes;
            if (!isset($avaliable[$row['attribute']]))
            {
                continue; // Sort out attributes not belonging to this question
            }
            if (!(array_key_exists('i18n', $attributes[$row['attribute']]) && $attributes[$row['attribute']]['i18n']))
            {
                $aResultAttributes[$row['attribute']]=$row['value'];
            }
            elseif(!empty($row['language']))
            {
                $aResultAttributes[$row['attribute']][$row['language']]=$row['value'];
            }
        }
        return $this->attributes=$aResultAttributes;
    }
      
    public function getTitle()
    {
        return $this->text;
    }
    
    public function getHelp()
    {
        return '';
    }
    
    public function getFileValidationMessage()
    {
        return '';
    }
    
    abstract public function availableAttributes();    
    abstract public function questionProperties();
}
?>