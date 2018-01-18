<?php
/**
 * File upload question Html
 * @var $fileid
 * @var $value
 * @var $filecountvalue
 */
?>

<!-- File Upload  -->

<!--answer -->
<div class="<?php echo $coreClass;?>">
    <div class='upload-button'>
        <a
            id='upload_<?php echo $fileid;?>'
            class='btn btn-primary upload'
            href='#'
            onclick='javascript:upload_<?php echo $fileid;?>();'
        >
            <span class='fa fa-upload'></span>&nbsp;<?php eT('Upload files'); ?>
        </a>
    </div>
    <input type='hidden' id='<?php echo $fileid;?>' name='<?php echo $fileid;?>' value='<?php echo $value;?>' />
    <input type='hidden' id='<?php echo $fileid;?>_filecount' name='<?php echo $fileid;?>_filecount' value="<?php echo $filecountvalue?>" />
    <div id='<?php echo $fileid;?>_uploadedfiles'>

    </div>
</div>

<!-- modal -->
<div id="file-upload-modal-<?php echo $fileid;?>" class="modal fade file-upload-modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header file-upload-modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h4 modal-title">' . ngT("Upload file|Upload files", $aQuestionAttributes['max_num_of_files']).'</div>
            </div>
            <div class="modal-body file-upload-modal-body">
                <iframe id="uploader<?php echo $fileid;?>" name="uploader<?php echo $fileid;?>" class="uploader-frame" src="'.$uploadurl.'" title="'.gT("Upload").'"></iframe>
            </div>
            <div class="modal-footer file-upload-modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">' . gT("Save changes").'</button>
            </div>
        </div>

    </div>
</div>
    

<!-- end of answer -->
<?php
/*
<script type='text/javascript'>
        function upload_$ia[1]() {
            var uploadurl = '{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&fieldname={$ia[1]}&qid={$ia[0]}';
            uploadurl += '&preview={$questgrppreview}&show_title={$aQuestionAttributes['show_title']}';
            uploadurl += '&show_comment={$aQuestionAttributes['show_comment']}';
            uploadurl += '&minfiles=' + LEMval('{$aQuestionAttributes['min_num_of_files']}');
            uploadurl += '&maxfiles=' + LEMval('{$aQuestionAttributes['max_num_of_files']}');
            $('#upload_$ia[1]').attr('href',uploadurl);
        }
        var uploadLang = {
             title: '".gT('Upload your files', 'js')."',
             returnTxt: '" . gT('Return to survey', 'js')."',
             headTitle: '" . gT('Title', 'js')."',
             headComment: '" . gT('Comment', 'js')."',
             headFileName: '" . gT('File name', 'js')."',
             deleteFile : '".gT('Delete')."',
             editFile : '".gT('Edit')."'
            };
        var imageurl =  '".Yii::app()->getConfig('imageurl')."';
        var uploadurl =  '".$scriptloc."';
    </script>

    
    $answer .= '<script type="text/javascript">
    var surveyid = '.Yii::app()->getConfig('surveyID').';
    $(document).on("ready pjax:scriptcomplete", function(){
    var fieldname = "'.$ia[1].'";
    var filecount = $("#"+fieldname+"_filecount").val();
    var json = $("#"+fieldname).val();
    var show_title = "'.$aQuestionAttributes["show_title"].'";
    var show_comment = "'.$aQuestionAttributes["show_comment"].'";
    displayUploadedFiles(json, filecount, fieldname, show_title, show_comment);
    });
    </script>';

    $answer .= '<script type="text/javascript">
    $(".basic_'.$ia[1].'").change(function() {
    var i;
    var jsonstring = "[";

    for (i = 1, filecount = 0; i <= LEMval("'.$aQuestionAttributes['max_num_of_files'].'"); i++)
    {
    if ($("#'.$ia[1].'_"+i).val() == "")
    continue;

    filecount++;
    if (i != 1)
    jsonstring += ", ";

    if ($("#answer'.$ia[1].'_"+i).val() != "")
    jsonstring += "{ ';

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_title'])) {
            $answer .= '\"title\":\""+$("#'.$ia[1].'_title_"+i).val()+"\",';
    } else {
            $answer .= '\"title\":\"\",';
    }

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_comment'])) {
            $answer .= '\"comment\":\""+$("#'.$ia[1].'_comment_"+i).val()+"\",';
    } else {
            $answer .= '\"comment\":\"\",';
    }

    $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
    }
    jsonstring += "]";

    $("#'.$ia[1].'").val(jsonstring);
    $("#'.$ia[1].'_filecount").val(filecount);
    });
    </script>';

    $uploadurl  = $scriptloc."?sid=".Yii::app()->getConfig('surveyID')."&fieldname=".$ia[1]."&qid=".$ia[0];
    $uploadurl .= "&preview=".$questgrppreview."&show_title=".$aQuestionAttributes['show_title'];
    $uploadurl .= "&show_comment=".$aQuestionAttributes['show_comment'];
    $uploadurl .= "&minfiles=".$aQuestionAttributes['min_num_of_files']; // TODO: Regression here? Should use LEMval(minfiles) like above
    $uploadurl .= "&maxfiles=".$aQuestionAttributes['max_num_of_files']; // Same here.

    $answer .= '
    <!-- Trigger the modal with a button -->

        <!-- Modal -->
        <div id="file-upload-modal-' . $ia[1].'" class="modal fade file-upload-modal" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header file-upload-modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="h4 modal-title">' . ngT("Upload file|Upload files", $aQuestionAttributes['max_num_of_files']).'</div>
                    </div>
                    <div class="modal-body file-upload-modal-body">
                        <iframe id="uploader' . $ia[1].'" name="uploader'.$ia[1].'" class="uploader-frame" src="'.$uploadurl.'" title="'.gT("Upload").'"></iframe>
                    </div>
                    <div class="modal-footer file-upload-modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">' . gT("Save changes").'</button>
                    </div>
                </div>

            </div>
        </div>
    ';
*/
?>
