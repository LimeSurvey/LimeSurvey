<?php
abstract class QuestionModule
{
    protected $data;
    protected $attributes;


    public function __construct($data = array())
    {
        foreach($data as $key => $datum)
            $this->$key = $datum;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        trigger_error(
            'Undefined property via __get()',
            E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    abstract public function getAnswerHTML();
    abstract public function getDataEntry($idrow, &$fnames, $language);

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
        $this->surveyid = $row['sid'];

        $aLanguages = array_merge((array)Survey::model()->findByPk($this->surveyid)->language, Survey::model()->findByPk($this->surveyid)->additionalLanguages);

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
            if (!in_array($row['attribute'], $available))
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
        $q = clone $this;
        if(isset($this->defaults) && isset($this->defaults[0])) $q->default=$map['defaultvalue']=$this->defaults[0];
        $map['q']=$q;
        return array($this->fieldname=>$map);
    }

    public function fileUpload()
    {
        return false;
    }

    public function filter($value, $type)
    {
        return $value;
    }

    public function getExtendedAnswer($value, $language)
    {
        return $value;
    }

    public function getQuotaValue($value)
    {
        return false;
    }

    public function retrieveText()
    {
        return $this->text;
    }

    public function loadAnswer($value)
    {
        return $value;
    }

    public function setAssessment()
    {
        return false;
    }

    public function getDBField()
    {
        return 'VARCHAR(5)';
    }

    public function prepareConditions($row)
    {
        if (preg_match("/^\+(.*)$/",$row['cfieldname'],$cfieldnamematch))
        { // this condition uses a single checkbox as source
            $row['cfieldname'] = $cfieldnamematch[1];
        }

        return array("cfieldname"=>$row['cfieldname'],
        "value"=>$row['value'],
        "matchfield"=>$row['cfieldname'],
        "matchvalue"=>$row['value'],
        "matchmethod"=>$row['method'],
        "subqid"=>$row['cfieldname'].'NAOK'
        );
    }

    public function transformResponseValue($export, $value, $options)
    {
        return $export->stripTagsFull($value);
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        return $answerCode;
    }

    public function getFieldSubHeading($survey, $export, $code)
    {
        if ($code && isset($this->aid) && !empty($this->aid)) return '['.$this->aid.']';
        else if (!$code)
        {
            $subQuestions = $survey->getSubQuestionArrays($this->id);
            foreach ($subQuestions as $question)
            {

                if ($question['title'] == $this->aid)
                {
                    $subQuestion = $question;
                }
            }
            if (isset($subQuestion) && isset($subQuestion['question']))
            {
                return ' ['.$export->stripTagsFull($subQuestion['question']).']';
            }
        }
        return '';
    }

    public function getSPSSAnswers()
    {
        return array();
    }

    public function getSPSSData($data, $iLength, $na)
    {
        $strTmp=mb_substr(stripTagsFull($data), 0, $iLength);
        if (trim($strTmp) != ''){
            $strTemp=str_replace(array("'","\n","\r"),array("''",' ',' '),trim($strTmp));
            return "'$strTemp'";
        }
        else
        {
            return $na;
        }
    }

    public function getAnswerArray($em)
    {
        return null;
    }

    public function adjustSize($size)
    {
        return $size;
    }

    public function jsVarNameOn()
    {
        return 'answer'.$this->fieldname;
    }

    public function jsVarName()
    {
        return 'java'.$this->fieldname;
    }

    public function onlyNumeric()
    {
        return false;
    }

    public function getCsuffix()
    {
        return '';
    }

    public function getSqsuffix()
    {
        return '';
    }

    public function getVarName()
    {
        return $this->title . ($this->aid != '' ? '_' . $this->aid : '');
    }

    public function getQuestion()
    {
        return $this->text;
    }

    public function getRowDivID()
    {
        return null;
    }

    public function generateQuestionInfo($type)
    {
        if (!is_null($this->rowdivid) || (isset($this->preg) && trim($this->preg) != ''))
        {
            return array(
                'q' => $this,
                'qid' => $this->id,
                'qseq' => $this->questioncount,
                'gseq' => $this->groupcount,
                'sgqa' => $this->surveyid . 'X' . $this->gid . 'X' . $this->id,
                'mandatory'=>$this->mandatory,
                'varName' => $this->getVarName(),
                'type' => $type,
                'fieldname' => $q->fieldname,
                'preg' => (isset($this->preg) && trim($this->preg) != '') ? $this->preg : NULL,
                'rootVarName' => $this->title,
                'subqs' => array()
                );
        } else {
            return null;
        }
    }

    public function generateSQInfo($ansArray)
    {
        if (!is_null($this->getRowdivid()) || (isset($this->preg) && trim($this->preg) != ''))
        {
            return array(
                'q' => $this,
                'rowdivid' => $this->getRowDivID,
                'varName' => $this->getVarName,
                'jsVarName_on' => $this->jsVarNameOn(),
                'jsVarName' => $this->jsVarName(),
                'csuffix' => $this->getCsuffix,
                'sqsuffix' => $this->getSqsuffix,
                );
        } else {
            return null;
        }
    }

    abstract public function availableAttributes($attr = false);
    abstract public function questionProperties($prop = false);
}
?>