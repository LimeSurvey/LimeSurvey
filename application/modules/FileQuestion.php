<?php
class FileQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        global $js_header_includes, $thissurvey;

        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes=$this->getAttributeValues();

        // Fetch question attributes
        $_SESSION['survey_'.$this->surveyid]['fieldname'] = $this->fieldname;

        $currentdir = getcwd();
        $pos = stripos($currentdir, "admin");
        $scriptloc = Yii::app()->getController()->createUrl('uploader/index');

        if ($pos)
        {
            $_SESSION['survey_'.$this->surveyid]['preview'] = 1 ;
            $questgrppreview = 1;   // Preview is launched from Question or group level

        }
        else if ($thissurvey['active'] != "Y")
            {
                $_SESSION['survey_'.$this->surveyid]['preview'] = 1;
                $questgrppreview = 0;
            }
            else
            {
                $_SESSION['survey_'.$this->surveyid]['preview'] = 0;
                $questgrppreview = 0;
        }

        $uploadbutton = "<h2><a id='upload_".$this->fieldname."' class='upload' ";
        $uploadbutton .= " href='#' onclick='javascript:upload_$this->fieldname();'";
        $uploadbutton .=">" .$clang->gT('Upload files'). "</a></h2>";

        $answer = "<script type='text/javascript'>
            function upload_$this->fieldname[1]() {
                var uploadurl = '{$scriptloc}?sid=".$this->surveyid."&amp;fieldname={$this->fieldname}&amp;qid={$this->id}';
                uploadurl += '&amp;preview={$questgrppreview}&amp;show_title={$aQuestionAttributes['show_title']}';
                uploadurl += '&amp;show_comment={$aQuestionAttributes['show_comment']}&amp;pos=".($pos?1:0)."';
                uploadurl += '&amp;minfiles=' + LEMval('{$aQuestionAttributes['min_num_of_files']}');
                uploadurl += '&amp;maxfiles=' + LEMval('{$aQuestionAttributes['max_num_of_files']}');
                $('#upload_$this->fieldname').attr('href',uploadurl);
            }
            var translt = {
                 title: '" . $clang->gT('Upload your files','js') . "',
                 returnTxt: '" . $clang->gT('Return to survey','js') . "',
                 headTitle: '" . $clang->gT('Title','js') . "',
                 headComment: '" . $clang->gT('Comment','js') . "',
                 headFileName: '" . $clang->gT('File name','js') . "',
                };
        </script>\n";

        header_includes(Yii::app()->getConfig('generalscripts')."modaldialog.js");

        // Modal dialog
        $answer .= $uploadbutton;

        $answer .= "<input type='hidden' id='".$this->fieldname."' name='".$this->fieldname."' value='".$_SESSION['survey_'.$this->surveyid][$this->fieldname]."' />";
        $answer .= "<input type='hidden' id='".$this->fieldname."_filecount' name='".$this->fieldname."_filecount' value=";

        if (array_key_exists($this->fieldname."_filecount", $_SESSION['survey_'.$this->surveyid]))
        {
            $tempval = $_SESSION['survey_'.$this->surveyid][$this->fieldname."_filecount"];
            if (is_numeric($tempval))
            {
                $answer .= $tempval . " />";
            }
            else
            {
                $answer .= "0 />";
            }
        }
        else {
            $answer .= "0 />";
        }

        $answer .= "<div id='".$this->fieldname."_uploadedfiles'></div>";

        $answer .= '<script type="text/javascript">
        var surveyid = '.$this->surveyid.';
        $(document).ready(function(){
        var fieldname = "'.$this->fieldname.'";
        var filecount = $("#"+fieldname+"_filecount").val();
        var json = $("#"+fieldname).val();
        var show_title = "'.$aQuestionAttributes["show_title"].'";
        var show_comment = "'.$aQuestionAttributes["show_comment"].'";
        var pos = "'.($pos ? 1 : 0).'";
        displayUploadedFiles(json, filecount, fieldname, show_title, show_comment, pos);
        });
        </script>';

        $answer .= '<script type="text/javascript">
        $(".basic_'.$this->fieldname.'").change(function() {
        var i;
        var jsonstring = "[";

        for (i = 1, filecount = 0; i <= LEMval("'.$aQuestionAttributes['max_num_of_files'].'"); i++)
        {
        if ($("#'.$this->fieldname.'_"+i).val() == "")
        continue;

        filecount++;
        if (i != 1)
        jsonstring += ", ";

        if ($("#answer'.$this->fieldname.'_"+i).val() != "")
        jsonstring += "{ ';

        if (isset($_SESSION['survey_'.$this->surveyid]['show_title']))
            $answer .= '\"title\":\""+$("#'.$this->fieldname.'_title_"+i).val()+"\",';
        else
            $answer .= '\"title\":\"\",';

        if (isset($_SESSION['survey_'.$this->surveyid]['show_comment']))
            $answer .= '\"comment\":\""+$("#'.$this->fieldname.'_comment_"+i).val()+"\",';
        else
            $answer .= '\"comment\":\"\",';

        $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
        }
        jsonstring += "]";

        $("#'.$this->fieldname.'").val(jsonstring);
        $("#'.$this->fieldname.'_filecount").val(filecount);
        });
        </script>';
        return $answer;
    }
    
    public function getFileValidationMessage()
    {
        global $filenotvalidated;

        $clang = Yii::app()->lang;
        $qtitle = "";
        if (isset($filenotvalidated) && is_array($filenotvalidated))
        {
            global $filevalidationpopup, $popup;

            foreach ($filenotvalidated as $k => $v)
            {
                if ($this->fieldname == $k || strpos($k, "_") && $this->fieldname == substr(0, strpos($k, "_") - 1));
                $qtitle .= '<br /><span class="errormandatory">'.$clang->gT($filenotvalidated[$k]).'</span><br />';
            }
        }
        return $qtitle;
    }
    
    public function createFieldmap($type=null)
    {
        $clang = Yii::app()->lang;
        $qidattributes= getQuestionAttributeValues($this->id);
        $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}";
        $field['fieldname']=$fieldname;
        $field['type']=$type;
        $field['sid']=$this->surveyid;
        $field['gid']=$this->gid;
        $field['qid']=$this->id;
        $field['aid']='';
        $field['title']=$this->title;
        $field['question']=$this->text;
        $field['group_name']=$this->groupname;
        $field['mandatory']=$this->mandatory;
        $field['hasconditions']=$this->conditionsexist;
        $field['usedinconditions']=$this->usedinconditions;
        $field['questionSeq']=$this->questioncount;
        $field['groupSeq']=$this->groupcount;
        $field['pq']=$this;
        $field['q']=$this;
        $field2=$field;
        $field['max_files']=$qidattributes['max_num_of_files'];
        $fieldname2="{$this->surveyid}X{$this->gid}X{$this->id}_filecount";
        $field2['fieldname']=$fieldname2;
        $field2['aid']='filecount';
        $field2['question']="filecount - ".$this->text;
        $q = clone $this;
        $q->fieldname = $fieldname;
        $q->aid=$field2['aid'];
        $q->question=$field2['question'];
        $field2['q']=$q;
        $map[$fieldname]=$field;
        $map[$fieldname2]=$field2;
        return $map;
    }
    
    public function fileUpload()
    {
        return true;
    }
        
    public function filterGET($value)
    {
        return NULL;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","show_title","show_comment","max_filesize","max_num_of_files","min_num_of_files","allowed_filetypes","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("File upload"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'file','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>