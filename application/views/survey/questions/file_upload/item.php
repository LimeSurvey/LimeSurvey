<?php
/**
 * File upload question Html
 * @var $fileid         $ia[1]
 * @var $value          htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],ENT_QUOTES,'utf-8')."
 * @var $filecountvalue
 */
?>

<!-- Upload button -->
<div class='upload-button'>
    <a
        id='upload_<?php echo $fileid;?>'
        class='upload'
        href='#'
        onclick='javascript:upload_<?php echo $fileid;?>();'
    >
        <?php eT('Upload files'); ?>
    </a>
</div>
<input type='hidden' id='<?php echo $fileid;?>' name='<?php echo $fileid;?>' value='<?php echo $value;?>' />
<input type='hidden' id='<?php echo $fileid;?>_filecount' name='<?php echo $fileid;?>_filecount' value="<?php echo $filecountvalue?>" />
<div id='<?php echo $fileid;?>_uploadedfiles'>
</div>
