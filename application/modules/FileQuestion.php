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

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output = "<table>\n";
        if ($fname['aid']!=='filecount' && isset($idrow[$this->fieldname . '_filecount']) && ($idrow[$this->fieldname . '_filecount'] > 0))
        {//file metadata
            $metadata = json_decode($idrow[$this->fieldname], true);
            $qAttributes = $this->getAttributeValues();
            for ($i = 0; $i < $qAttributes['max_num_of_files'], isset($metadata[$i]); $i++)
            {
                if ($qAttributes['show_title'])
                    $output .= '<tr><td>Title    </td><td><input type="text" class="'.$this->fieldname.'" id="'.$this->fieldname.'_title_'.$i   .'" name="title"    size=50 value="'.htmlspecialchars($metadata[$i]["title"])   .'" /></td></tr>';
                if ($qAttributes['show_comment'])
                    $output .= '<tr><td >Comment  </td><td><input type="text" class="'.$this->fieldname.'" id="'.$this->fieldname.'_comment_'.$i .'" name="comment"  size=50 value="'.htmlspecialchars($metadata[$i]["comment"]) .'" /></td></tr>';

                $output .= '<tr><td>        File name</td><td><input   class="'.$this->fieldname.'" id="'.$this->fieldname.'_name_'.$i.'" name="name" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["name"]))    .'" /></td></tr>'
                .'<tr><td></td><td><input type="hidden" class="'.$this->fieldname.'" id="'.$this->fieldname.'_size_'.$i.'" name="size" size=50 value="'.htmlspecialchars($metadata[$i]["size"])    .'" /></td></tr>'
                .'<tr><td></td><td><input type="hidden" class="'.$this->fieldname.'" id="'.$this->fieldname.'_ext_'.$i.'" name="ext" size=50 value="'.htmlspecialchars($metadata[$i]["ext"])     .'" /></td></tr>'
                .'<tr><td></td><td><input type="hidden"  class="'.$this->fieldname.'" id="'.$this->fieldname.'_filename_'.$i.'" name="filename" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["filename"]))    .'" /></td></tr>';
            }
            $output .= '<tr><td></td><td><input type="hidden" id="'.$this->fieldname.'" name="'.$this->fieldname.'" size=50 value="'.htmlspecialchars($idrow[$this->fieldname]).'" /></td></tr>';
            $output .= '</table>';
            $output .= '<script type="text/javascript">
            $(function() {
            $(".'.$this->fieldname.'").keyup(function() {
            var filecount = $("#'.$this->fieldname.'_filecount").val();
            var jsonstr = "[";
            var i;
            for (i = 0; i < filecount; i++)
            {
            if (i != 0)
            jsonstr += ",";
            jsonstr += \'{"title":"\'+$("#'.$this->fieldname.'_title_"+i).val()+\'",\';
            jsonstr += \'"comment":"\'+$("#'.$this->fieldname.'_comment_"+i).val()+\'",\';
            jsonstr += \'"size":"\'+$("#'.$this->fieldname.'_size_"+i).val()+\'",\';
            jsonstr += \'"ext":"\'+$("#'.$this->fieldname.'_ext_"+i).val()+\'",\';
            jsonstr += \'"filename":"\'+$("#'.$this->fieldname.'_filename_"+i).val()+\'",\';
            jsonstr += \'"name":"\'+encodeURIComponent($("#'.$this->fieldname.'_name_"+i).val())+\'"}\';
            }
            jsonstr += "]";
            $("#'.$this->fieldname.'").val(jsonstr);

            });
            });
            </script>';
        }
        else
        {//file count
            $output .= '<input readonly id="'.$this->fieldname.'" name="'.$this->fieldname.'" value ="'.htmlspecialchars($idrow[$this->fieldname]).'" /></td></table>';
        }
        return $output;
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
        
    public function filter($value, $type)
    {
        switch ($type)
        {
            case 'get':
            return NULL;
            case 'dataentry':
            if (strpos($this->fieldname, '_filecount') && $value == "")
            {
                return NULL;
            }
            case 'db':
            return $value;
            case 'dataentryinsert':
            if (!strpos($this->fieldname, "_filecount"))
            {
                $this->dataentry = json_decode(stripslashes($value));
                $filecount = 0;

                for ($i = 0; $filecount < count($this->dataentry); $i++)
                {
                    if ($_FILES[$q->fieldname."_file_".$i]['error'] != 4)
                    {
                        $target = Yii::app()->getConfig('uploaddir')."/surveys/". $this->surveyid ."/files/".randomChars(20);
                        $size = 0.001 * $_FILES[$this->fieldname."_file_".$i]['size'];
                        $name = rawurlencode($_FILES[$this->fieldname."_file_".$i]['name']);

                        if (move_uploaded_file($_FILES[$this->fieldname."_file_".$i]['tmp_name'], $target))
                        {
                            $this->dataentry[$filecount]->filename = basename($target);
                            $this->dataentry[$filecount]->name = $name;
                            $this->dataentry[$filecount]->size = $size;
                            $pathinfo = pathinfo($_FILES[$q->fieldname."_file_".$i]['name']);
                            $phparray[$filecount]->ext = $pathinfo['extension'];
                            $filecount++;
                        }
                    }
                }
                return ls_json_encode($this->dataentry);
            }
            else
            {
                return count($this->dataentry);
            }
        }
    }
                
    public function getExtendedAnswer($value, $language)
    {
        if (substr($this->fieldname, -9) == 'filecount') return $language->gT("File count")." [$value]";
        //Show the filename, size, title and comment -- no link!
        $files = json_decode($value);
        if (is_array($files)) {
            foreach ($files as $file) {
                $answer .= $file->name .
                ' (' . $file->size . 'KB) ' .
                strip_tags($file->title) .
                ' - ' . strip_tags($file->comment) . "<br/>";
            }
            return $answer;
        }
        return '';
    }
    
    public function getDBField()
    {
        if (strpos($this->fieldname, "_"))
            return "INT(1)";
        else
           return "text";
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