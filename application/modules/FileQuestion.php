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
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldname'] = $this->fieldname;

        $currentdir = getcwd();
        $pos = stripos($currentdir, "admin");
        $scriptloc = Yii::app()->getController()->createUrl('uploader/index');

        if ($pos)
        {
            $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1 ;
            $questgrppreview = 1;   // Preview is launched from Question or group level

        }
        else if ($thissurvey['active'] != "Y")
            {
                $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1;
                $questgrppreview = 0;
            }
            else
            {
                $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 0;
                $questgrppreview = 0;
        }

        $uploadbutton = "<h2><a id='upload_".$this->fieldname."' class='upload' href='{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&amp;fieldname={$this->fieldname}&amp;qid={$this->id}&amp;preview="
        ."{$questgrppreview}&amp;show_title={$aQuestionAttributes['show_title']}&amp;show_comment={$aQuestionAttributes['show_comment']}&amp;pos=".($pos?1:0)."'>" .$clang->gT('Upload files'). "</a></h2>";

        $answer =  "<script type='text/javascript'>
        var translt = {
        title: '" . $clang->gT('Upload your files','js') . "',
        returnTxt: '" . $clang->gT('Return to survey','js') . "',
        headTitle: '" . $clang->gT('Title','js') . "',
        headComment: '" . $clang->gT('Comment','js') . "',
        headFileName: '" . $clang->gT('File name','js') . "'
        };
        </script>\n";

        $js_header_includes[]= "<script type='text/javascript' src='".Yii::app()->getBaseUrl(true)."/scripts/modaldialog.js'></script>";

        // Modal dialog
        $answer .= $uploadbutton;

        $answer .= "<input type='hidden' id='".$this->fieldname."' name='".$this->fieldname."' value='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname]."' />";
        $answer .= "<input type='hidden' id='".$this->fieldname."_filecount' name='".$this->fieldname."_filecount' value=";

        if (array_key_exists($this->fieldname."_filecount", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]))
        {
            $tempval = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname."_filecount"];
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
        var surveyid = '.Yii::app()->getConfig('surveyID').';
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

        for (i = 1, filecount = 0; i <= '.$aQuestionAttributes['max_num_of_files'].'; i++)
        {
        if ($("#'.$this->fieldname.'_"+i).val() == "")
        continue;

        filecount++;
        if (i != 1)
        jsonstring += ", ";

        if ($("#answer'.$this->fieldname.'_"+i).val() != "")
        jsonstring += "{ ';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_title']))
            $answer .= '\"title\":\""+$("#'.$this->fieldname.'_title_"+i).val()+"\",';
        else
            $answer .= '\"title\":\"\",';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_comment']))
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
    
    public function getTitle()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes=$this->getAttributeValues();
        if ($aQuestionAttributes['min_num_of_files'] != 0)
        {
            if (trim($aQuestionAttributes['min_num_of_files']) != 0)
            {
                return $this->text."<br />\n<span class = \"questionhelp\">".sprintf($clang->gT("At least %d files must be uploaded for this question"), $aQuestionAttributes['min_num_of_files'])."<span>";
            }
        }
        return $this->text;
    }
    
    public function getHelp()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes=$this->getAttributeValues();
        if ($aQuestionAttributes['min_num_of_files'] != 0)
        {
            if (trim($aQuestionAttributes['min_num_of_files']) != 0)
            {
                return ' '.sprintf($clang->gT("At least %d files must be uploaded for this question"), $aQuestionAttributes['min_num_of_files']);
            }
        }
        return '';
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
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","show_title","show_comment","max_filesize","max_num_of_files","min_num_of_files","allowed_filetypes","random_group");
    }

    public function questionProperties()
    {
        $clang=Yii::app()->lang;
        return array('description' => $clang->gT("File upload"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
    }
}
?>