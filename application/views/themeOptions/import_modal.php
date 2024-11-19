<?php
    /**
     * @var AdminController $this
     * @var string          $importTemplate
     * @var string          $themeType
     *
     */

?>
<div class="modal fade" tabindex="-1" role="dialog" id="<?php echo $importModal;?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo CHtml::form(
                    array('admin/themes/sa/upload'),
                    'post',
                    array(
                            'id'=>$importTemplate,
                            'name'=>$importTemplate,
                            'enctype'=>'multipart/form-data',
                            'onsubmit'=>'return window.LS.validatefilename(this,"'.gT('Please select a file to import!', 'js').'");'
                    )
            ); ?>
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT("Upload and install theme file")]
            );
            ?>
                <div class="modal-body">
                    <input type='hidden' name='lid' value='$lid' />
                    <input type='hidden' name='action' value='templateupload' />
                    <?php if (isset($themeType)) : ?>
                        <input type='hidden' name='theme' value='<?php echo $themeType; ?>'/>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for='the_file' class="form-label">
                            <?php eT("Select theme ZIP file:") ?>
                        </label>
                        <input id='the_file' class="form-control" name='the_file' type="file" accept='.zip' />
                        <div class="form-text mt-2">
                            <?php printf(gT('(Maximum file size: %01.2f MB)'),getMaximumFileUploadSize()/1024/1024); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?php eT("Cancel");?>
                    </button>
                    <?php if (!class_exists('ZipArchive')) {?>
                        <?php eT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") ?>
                    <?php } else {?>
                        <input class="btn btn-primary" type='button' value='<?php eT("Install") ?>' onclick='if (window.LS.validatefilename(this.form,"<?php eT('Please select a file to import!', 'js ') ?>")) { this.form.submit();}' />
                    <?php }?>
                </div>
            </form>
        </div>
    </div>
</div>
