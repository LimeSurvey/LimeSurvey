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
<div class='upload-button'>
    <a
        id='upload_<?php echo $fileid;?>'
        class='btn btn-default upload'
        href='#'
        onclick='javascript:upload_<?php echo $fileid;?>();'
    >
        <span class='fa fa-upload'></span>&nbsp;<?php eT('Upload files'); ?>
    </a>
</div>
<?php 
    echo CHtml::hiddenField($fileid, $value, array('name'=>$fileid));
    echo CHtml::hiddenField($fileid.'_filecount', $filecountvalue, array('name'=>$fileid.'_filecount'));
?>
<div id='<?php echo $fileid;?>_uploadedfiles'>
</div>
<!-- end of answer -->
