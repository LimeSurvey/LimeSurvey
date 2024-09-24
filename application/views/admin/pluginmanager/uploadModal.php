<div class="modal fade" tabindex="-1" role="dialog" id="installPluginZipModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Install plugin ZIP file") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php echo CHtml::form(
                Yii::app()->getController()->createUrl(
                    '/admin/pluginmanager',
                    ['sa' => 'upload']
                ),
                'post',
                [
                    'id'       =>'uploadpluginzip',
                    'name'     =>'uploadpluginzip',
                    'enctype'  =>'multipart/form-data',
                    'onsubmit' =>'return window.LS.validatefilename(this,"'.gT('Please select a plugin to install!', 'js').'");'
                ]
            ); ?>
            <div class="modal-body">
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT('Warning: Only install plugins from sources you trust!'),
                    'type' => 'warning',
                ]);
                ?>
                <input type='hidden' name='lid' value='$lid' />
                <input type='hidden' name='action' value='templateupload' />
                <div  class="mb-3">
                    <label for='the_file'><?php eT("Select plugin ZIP file:") ?></label>
                    <input id='the_file' class="form-control" name='the_file' type="file" accept='.zip' />
                    <br/>
                    <?php printf(gT('(Maximum file size: %01.2f MB)'), getMaximumFileUploadSize()/1024/1024); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php eT("Close");?>
                </button>
                <?php if (!class_exists('ZipArchive')): ?>
                    <?php eT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") ?>
                <?php else: ?>
                    <button type="button" class='btn btn-primary' onclick='if (window.LS.validatefilename(this.form,"<?php eT('Please select a file to import!', 'js') ?>")) { this.form.submit();}'>
                        <?php eT("Install") ?>
                    </button>
                <?php endif; ?>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
