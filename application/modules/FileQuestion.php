<?php
class FileQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        global $thissurvey;

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
            function upload_{$this->fieldname}() {
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
                 headFileName: '" . $clang->gT('File name','js') . "'
                };
			var imageurl =  '".Yii::app()->getConfig('imageurl')."';
			var uploadurl =  '".$scriptloc."';
        </script>\n";

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

    public function getHeaderIncludes()
    {
        return array(Yii::app()->getConfig('generalscripts')."modaldialog.js" => 'js');
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

    public function createFieldmap()
    {
        $clang = Yii::app()->lang;
        $qidattributes=$this->getAttributeValues();
        $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}";
        $fieldname2="{$this->surveyid}X{$this->gid}X{$this->id}_filecount";
        $q = clone $this;
        $q2 = clone $this;
        $q->maxfiles = $qidattributes['max_num_of_files'];
        $q2->fieldname = $fieldname2;
        $q2->aid='filecount';
        $q2->text="filecount - ".$this->text;
        $map[$fieldname]=$q;
        $map[$fieldname2]=$q2;
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
            case 'post':
            case 'dataentryinsert':
            if (!strpos($this->fieldname, "_filecount"))
            {
                $dataentry = json_decode(stripslashes($value));
                $filecount = 0;

                for ($i = 0; $filecount < count($dataentry); $i++)
                {
                    if ($_FILES[$q->fieldname."_file_".$i]['error'] != 4)
                    {
                        $target = Yii::app()->getConfig('uploaddir')."/surveys/". $this->surveyid ."/files/".randomChars(20);
                        $size = 0.001 * $_FILES[$this->fieldname."_file_".$i]['size'];
                        $name = rawurlencode($_FILES[$this->fieldname."_file_".$i]['name']);

                        if (move_uploaded_file($_FILES[$this->fieldname."_file_".$i]['tmp_name'], $target))
                        {
                            $dataentry[$filecount]->filename = basename($target);
                            $dataentry[$filecount]->name = $name;
                            $dataentry[$filecount]->size = $size;
                            $pathinfo = pathinfo($_FILES[$q->fieldname."_file_".$i]['name']);
                            $phparray[$filecount]->ext = $pathinfo['extension'];
                            $filecount++;
                        }
                    }
                }
                return ls_json_encode($dataentry);
            }
            else
            {
                return count(json_decode(stripslashes($value)));
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

    public function jsVarNameOn()
    {
        return $this->fieldname;
    }

    public function jsVarName()
    {
        return $this->fieldname;
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

    public function generateSQInfo($ansArray)
    {
        return array(array(
            'q' => $this,
            'rowdivid' => $this->getRowDivID(),
            'varName' => $this->getVarName(),
            'jsVarName_on' => $this->jsVarNameOn(),
            'jsVarName' => $this->jsVarName(),
            'csuffix' => $this->getCsuffix(),
            'sqsuffix' => $this->getSqsuffix(),
            ));
    }

    public function availableOptions()
    {
        return array('other' => false, 'valid' => false, 'mandatory' => false);
    }

    public function getDataEntryView($language)
    {
        $qidattributes = $this->getAttributeValues();
        $title = $qidattributes['show_title'] ? "'+$('#{$this->fieldname}_title_'+i).val()+'" : '';
        $comment = $qidattributes['show_comment'] ? "'+$('#{$this->fieldname}_comment_'+i).val()+'" : '';
        $output = <<<OUTPUT
        <script type='text/javascript'>

            function updateJSON{$this->fieldname}() {

                var jsonstr = '[';
                var i;
                var filecount = 0;

                for (i = 0; i < {$qidattributes['max_num_of_files']}; i++)
                {
                    if ($('#{$this->fieldname}_file_'+i).val() != '')
                    {
                        jsonstr += '{ \"title\":\"{$title}\",';";
                        jsonstr += '\"comment\":\"{$comment}\",';";
                        jsonstr += '"name":"'+$('#{$this->fieldname}_file_'+i).val()+'"}';

                        jsonstr += ',';
                        filecount++;
                    }
                }

                if (jsonstr.charAt(jsonstr.length - 1) == ',')
                    jsonstr = jsonstr.substring(0, jsonstr.length - 1);

                jsonstr += ']';
                $('#{$this->fieldname}').val(jsonstr);
                $('#{$this->fieldname}_filecount').val(filecount);
            }
        </script>

        <table border='0'>
OUTPUT;
        if ($qidattributes['show_title'] && $qidattributes['show_title']) {
            $output .= "<tr><th>Title</th><th>Comment</th>";
        } else if ($qidattributes['show_title']) {
            $output .= "<tr><th>Title</th>";
        } else if ($qidattributes['show_comment']) {
            $output .= "<tr><th>Comment</th>";
        }

        $output .= "<th>Select file</th></tr>";
        for ($i = 0; $i < $qidattributes['max_num_of_files']; $i++)
        {
            $output .= "<tr>";
            if ($qidattributes['show_title'])
                $output .= "<td align='center'><input type='text' id='{$this->fieldname}_title_{$i}' maxlength='100' onChange='updateJSON{$this->fieldname}()' /></td>";

            if ($qidattributes['show_comment'])
                $output .= "<td align='center'><input type='text' id='{$this->fieldname}_comment_{$i}' maxlength='100' onChange='updateJSON{$this->fieldname}()' /></td>";

            $output .= "<td align='center'><input type='file' name='{$this->fieldname}_file_{$i}' id='{$this->fieldname}_file_{$i}' onChange='updateJSON{$this->fieldname}()' /></td></tr>";
        }
        $output .= "<tr><td align='center'><input type='hidden' name='{$this->fieldname}' id='{$this->fieldname}' value='' /></td></tr>";
        $output .= "<tr><td align='center'><input type='hidden' name='{$this->fieldname}_filecount' id='{$this->fieldname}_filecount' value='' /></td></tr>";
        $output .= "</table>";
        
        return $output;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","show_title","show_comment","max_filesize","max_num_of_files","min_num_of_files","allowed_filetypes","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("File upload"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'generic_question','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>