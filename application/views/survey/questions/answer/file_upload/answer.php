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
<!-- end of answer -->
