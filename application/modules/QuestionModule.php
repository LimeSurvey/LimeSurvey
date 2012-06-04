<?php
abstract class QuestionModule
{
    public $surveyid;
    public $id;
    public $fieldname;
    public $title;
    public $text;
    public $gid;
    public $mandatory;
    public $conditionsexist; //boolean
    public $usedinconditions;
    public $questioncount;
    public $groupcount;
    public $randomgid;
    public $language;
    public $groupname;
    public $aid;
    public $default;
    public $preg;
    public $other;
    protected $attributes;


    public function __construct($surveyid = null, $id = null, $fieldname = null, $title = null,
    $text = null, $gid = null, $mandatory = null, $conditionsexist = null, $usedinconditions = null,
    $questioncount = null, $randomgid = null, $language = null)
    {
        $this->surveyid=$surveyid;
        $this->id=$id;
        $this->fieldname=$fieldname;
        $this->title=$title;
        $this->text=$text;
        $this->gid=$gid;
        $this->mandatory=$mandatory;
        $this->conditionsexist=$conditionsexist; //boolean
        $this->usedinconditions=$usedinconditions;
        $this->questioncount=$questioncount;
        $this->randomgid=$randomgid;
        $this->language=$language;
    }
    
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
    
    public function mandatoryPopup($notanswered=null)
    {
        global $showpopups;

        if (is_array($notanswered) && isset($showpopups) && $showpopups == 1) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        {
            global $mandatorypopup;
            return $mandatorypopup="Y";
        }
        return false;
    }
    
    public function getPopup($notanswered=null)
    {
        global $showpopups;

        $clang = Yii::app()->lang;

        if (is_array($notanswered) && isset($showpopups) && $showpopups == 1) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        {
            global $popup;
            //POPUP WARNING

            return $popup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.", "js")."\");});\n //-->\n
            </script>\n";
        }
        return false;
    }
    
    public function createFieldmap($type=null)
    {
        $map['fieldname']=$this->fieldname;
        $map['type']=$type;
        $map['sid']=$this->surveyid;
        $map['gid']=$this->gid;
        $map['qid']=$this->id;
        $map['aid']=$this->aid;
        $map['title']=$this->title;
        $map['question']=$this->text;
        $map['group_name']=$this->groupname;
        $map['mandatory']=$this->mandatory;
        $map['hasconditions']=$this->conditionsexist;
        $map['usedinconditions']=$this->usedinconditions;
        $map['questionSeq']=$this->questioncount;
        $map['groupSeq']=$this->groupcount;
        if(isset($this->default[0])) $map['defaultvalue']=$this->default[0];
        $map['q']=$this;
        $map['pq']=$this;
        return array($this->fieldname=>$map);
    }
    
    public function fileUpload()
    {
        return false;
    }
    
    public function filterGET($value)
    {
        return $value;
    }
        
    public function prepareValue($value)
    {
        return $value;
    }
    
    abstract public function availableAttributes($attr = false);    
    abstract public function questionProperties($prop = false);
}
?>