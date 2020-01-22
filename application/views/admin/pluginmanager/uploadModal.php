<div class="modal fade" tabindex="-1" role="dialog" id="installPluginZipModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title h4"><?php eT("Install plugin ZIP file") ?></div>
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
                <div class='alert alert-warning'>
                    <i class='fa fa-warning'></i>
                    <?php eT('Warning: Only install plugins from sources you trust!'); ?>
                </div>
                <input type='hidden' name='lid' value='$lid' />
                <input type='hidden' name='action' value='templateupload' />
                <div  class="form-group">
                    <label for='the_file'><?php eT("Select plugin ZIP file:") ?></label>
                    <input id='the_file' name='the_file' type="file" accept='.zip' />
                    <br/>
                    <?php printf(gT('(Maximum file size: %01.2f MB)'), getMaximumFileUploadSize()/1024/1024); ?>
                </div>
            </div>
            <div class="modal-footer">
                <?php if (!function_exists("zip_open")): ?>
                    <?php eT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") ?>
                <?php else: ?>
                    <button class='btn btn-success' onclick='if (window.LS.validatefilename(this.form,"<?php eT('Please select a file to import!', 'js') ?>")) { this.form.submit();}'>
                        <i class='fa fa-upload'></i>&nbsp;<?php eT("Upload") ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
