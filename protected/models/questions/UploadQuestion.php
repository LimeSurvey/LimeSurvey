<?php
namespace ls\models\questions;

class UploadQuestion extends \Question
{
    /**
     * Returns the fields for this question.
     * @return QuestionResponseField[]
     */
    public function getFields()
    {
        $result = parent::getFields();
        $result[] = $field = new \ls\components\QuestionResponseField($this->sgqa . '_filecount', $this->title . '_filecount', $this);
        $field->setRelevanceScript($this->getRelevanceScript());

        return $result;
    }


    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'upload-files';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);

        $options = [];
        if ($this->max_num_of_files > 1) {
            $options['multiple'] = true;
        }

        if (!empty($this->allowed_filetypes)) {
            $types = [];
            foreach(explode(',', $this->allowed_filetypes) as $extension) {
                $types[] =  "." . trim($extension);
            }
            $options['accept'] = implode(',', $types);
        }
        // We always add the brackets regardless if we accept multiple files.
        $result->setHtml(\TbHtml::fileField($this->sgqa . '[]', "", $options));
        return $result;
        $scriptloc = App()->createUrl('uploader/index', [
            'sid' =>  $this->survey->primaryKey,
            'fieldname' => $this->sgqa,
            'qid' => $this->primaryKey,
            'show_title' => $this->show_title,
            'show_comment' => $this->show_comment,
        ]);



        $uploadbutton = "<div class='upload-button'><a id='upload_".$this->sgqa."' class='upload' ";
        $uploadbutton .= " href='#' onclick='javascript:upload_$this->sgqa();'";
        $uploadbutton .=">" .gT('Upload files'). "</a></div>";

        $html = "<script type='text/javascript'>
        function upload_$this->sgqa() {
            var uploadurl = '{$scriptloc}'
                + '&minfiles='
                + EM.val('{$this->min_num_of_files}')
                + '&maxfiles=' + EM.val('{$this->max_num_of_files}');
            $('#upload_$this->sgqa').attr('href',uploadurl);
        }
        var uploadLang = {
             title: '" . gT('Upload your files','js') . "',
             returnTxt: '" . gT('Return to survey','js') . "',
             headTitle: '" . gT('Title','js') . "',
             headComment: '" . gT('Comment','js') . "',
             headFileName: '" . gT('File name','js') . "',
             deleteFile : '".gt('Delete')."',
             editFile : '".gt('Edit')."'
            };
        var imageurl =  '". App()->getConfig('imageurl')."';
        var uploadurl =  '".$scriptloc."';
    </script>\n";
        App()->getClientScript()->registerCoreScript('jqueryui');
        App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts')."modaldialog.js");
        App()->getClientScript()->registerCssFile(App()->getConfig('publicstyleurl') . "uploader-files.css");
        // Modal dialog
        $html .= $uploadbutton;

        $html .= "<input type='hidden' id='".$this->sgqa."' name='".$this->sgqa."' value='".htmlspecialchars($response->{$this->sgqa},ENT_QUOTES,'utf-8')."' />";
        $html .= "<input type='hidden' id='".$this->sgqa."_filecount' name='".$this->sgqa."_filecount' value=";

        if (isset($response{$this->sgqa}))
        {
            $tempval = $response{$this->sgqa};
            if (is_numeric($tempval))
            {
                $html .= $tempval . " />";
            }
            else
            {
                $html .= "0 />";
            }
        }
        else {
            $html .= "0 />";
        }

        $html .= "<div id='".$this->sgqa."_uploadedfiles'></div>";

        $html .= '<script type="text/javascript">
    $(document).ready(function(){
    var fieldname = "'.$this->sgqa.'";
    var filecount = $("#"+fieldname+"_filecount").val();
    var json = $("#"+fieldname).val();
    var show_title = "'.$this->show_title.'";
    var show_comment = "'.$this->show_comment.'";
    displayUploadedFiles(json, filecount, fieldname, show_title, show_comment);
    });
    </script>';

        $html .= '<script type="text/javascript">
    $(".basic_'.$this->sgqa.'").change(function() {
    var i;
    var jsonstring = "[";

    for (i = 1, filecount = 0; i <= EM.val("'.$this->max_num_of_files.'"); i++)
    {
    if ($("#'.$this->sgqa.'_"+i).val() == "")
    continue;

    filecount++;
    if (i != 1)
    jsonstring += ", ";

    if ($("#answer'.$this->sgqa.'_"+i).val() != "")
    jsonstring += "{ ';

        if (isset($_SESSION['survey_'.App()->getConfig('surveyID')]['show_title']))
            $html .= '\"title\":\""+$("#'.$this->sgqa.'_title_"+i).val()+"\",';
        else
            $html .= '\"title\":\"\",';

        if (isset($_SESSION['survey_'.App()->getConfig('surveyID')]['show_comment']))
            $html .= '\"comment\":\""+$("#'.$this->sgqa.'_comment_"+i).val()+"\",';
        else
            $html .= '\"comment\":\"\",';

        $html .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
    }
    jsonstring += "]";

    $("#'.$this->sgqa.'").val(jsonstring);
    $("#'.$this->sgqa.'_filecount").val(filecount);
    });
    </script>';

        $result->setHtml($html);
        return $result;
    }

    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();

        // Add validation for file count.
        return $result;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        return [
            $this->sgqa => "text",
            "{$this->sgqa}_filecount" => "int"
        ];
    }


}