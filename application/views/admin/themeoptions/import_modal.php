<?php
/**
* @var AdminController $this
*/
?>

<div class="modal fade" tabindex="-1" role="dialog" id="importModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo CHtml::form(array('admin/themes/sa/upload'), 'post', array('id'=>'importtemplate', 'name'=>'importtemplate', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return window.LS.validatefilename(this,"'.gT('Please select a file to import!', 'js').'");')); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h4">
                        <?php eT("Upload template file") ?>
                    </div>
                </div>
                <div class="modal-body">
                    <input type='hidden' name='lid' value='$lid' />
                    <input type='hidden' name='action' value='templateupload' />
                    <div class="form-group">
                        <label for='the_file'>
                            <?php eT("Select template ZIP file:") ?>
                        </label>
                        <input id='the_file' name='the_file' type="file" accept='.zip' />
                        <?php printf(gT('(Maximum file size: %01.2f MB)'),getMaximumFileUploadSize()/1024/1024); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if (!function_exists("zip_open")) {?>
                        <?php eT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") ?>
                    <?php } else {?>
                        <input class="btn btn-success" type='button' value='<?php eT("Import") ?>' onclick='if (window.LS.validatefilename(this.form,"<?php eT(' Please select a file to import! ', 'js ') ?>")) { this.form.submit();}' />
                    <?php }?>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php eT("Close");?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
