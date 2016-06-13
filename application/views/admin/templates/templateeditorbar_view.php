<script type="text/javascript">
    var adminlanguage='<?php echo $codelanguage; ?>';
    var highlighter='<?php echo $highlighter; ?>';
</script>
<script type='text/javascript'>
    <!--
    function copyprompt(text, defvalue, copydirectory, action)
    {
        if (newtemplatename=window.prompt(text, defvalue))
        {
            sendPost('<?php echo $this->createUrl('admin/templates/sa/template'); ?>'+action,'',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));
        }
    }

    $(document).ready(function(){
        $("#importtemplatefile").submit(function(){

            filename = $("#upload_file").val();
            if(filename==""){
                return false; // False click
            }
            var allowedtypes=',<?php echo Yii::app()->getConfig('allowedtemplateuploads'); ?>,';
            var lastdotpos=-1;
            var ext='';
            if ((lastdotpos=filename.lastIndexOf('.')) < 0)
            {
                alert('<?php eT('This file type is not allowed to be uploaded.','js'); ?>');
                return false;
            }
            else
            {
                ext = ',' + filename.substr(lastdotpos+1) + ',';
                ext = ext.toLowerCase();
                if (allowedtypes.indexOf(ext) < 0)
                {
                    alert('<?php eT('This file type is not allowed to be uploaded.','js'); ?>');
                    return false;
                }
                else
                {
                    return true;
                }
            }
        });
    });
    //-->
</script>


<div class='menubar' id="templateeditorbar">
    <div class='row container-fluid'>

        <!-- Left Menu -->
        <div class="col-md-5">

            <!-- Create -->
            <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                <?php if(is_writable($usertemplaterootdir) ):?>
                    <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Create template called:"); ?>', '<?php eT("NewTemplate"); ?>', 'default', 'copy')">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create"); ?>
                    </a>
                    <?php else: ?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("The template upload directory doesn't exist or is not writable."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-addt text-success"></span>
                            <?php eT("Create"); ?>
                        </button>
                    </span>
                    <?php endif;?>
                <?php endif;?>

            <!-- Import -->
            <?php $importModal=false;?>
            <?php if(is_writable($tempdir)):?>
                <?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <?php if (is_writable($usertemplaterootdir) && function_exists("zip_open")):?>
                        <?php $importModal=true;?>
                        <a class="btn btn-default" href="" role="button" data-toggle="modal" data-target="#importModal">
                            <span class="icon-import text-success"></span>
                            <?php eT("Import"); ?>
                        </a>
                        <?php else:
                        if (function_exists("zip_open")){
                            $sMessage=gT("The template upload directory doesn't exist or is not writable.");
                        }
                        else{
                            $sMessage=gT("You do not have the required ZIP library installed in PHP.");
                        }
                        ?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                                <span class="icon-import text-success"></span>
                                <?php eT("Import"); ?>
                            </button>
                        </span>
                        <?php endif;?>
                    <?php else: ?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("We are sorry but you don't have permissions to do this."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-import text-success"></span>
                            <?php eT("Import"); ?>
                        </button>
                    </span>
                    <?php endif;?>

                <!-- Export -->
                <?php if(Permission::model()->hasGlobalPermission('templates','export') && function_exists("zip_open")):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/templates/sa/templatezip/templatename/' . $templatename) ?>" role="button">
                        <span class="icon-export text-success"></span>
                        <?php eT("Export"); ?>
                    </a>
                    <?php endif;?>

                <!-- Copy -->
                <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                    <?php if (is_writable($usertemplaterootdir)):?>
                        <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Please enter the name for the copied template:"); ?>', '<?php echo gT("copy_of_")."$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
                            <span class="icon-copy text-success"></span>
                            <?php eT("Copy"); ?>
                        </a>
                        <?php else: ?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("The template upload directory doesn't exist or is not writable."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                                <span class="icon-copy text-success"></span>
                                <?php eT("Copy"); ?>
                            </button>
                        </span>
                        <?php endif;?>
                    <?php endif;?>

                <?php else: ?>

                <!-- All buttons disabled -->

                <!-- import disabled -->
                <?php

                if (!function_exists("zip_open"))
                {
                    $sMessage=gT("You cannot upload templates because you do not have the required ZIP library installed in PHP.");
                }
                else
                {
                    $sMessage=gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/templates in order to enable this option.");
                }
                if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-import text-muted"></span>
                            <?php eT("Import"); ?>
                        </button>
                    </span>
                    <?php endif;?>

            <!-- export disabled -->
            <?php if(Permission::model()->hasGlobalPermission('templates','export')):?>
                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>" >
                    <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                        <span class="icon-export text-muted"></span>
                        <?php eT("Export"); ?>
                    </button>
                </span>
                <?php endif;?>

            <!-- create disabled -->
            <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>" >
                    <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                        <span class="icon-copy text-muted"></span>
                        <?php eT("Copy"); ?>
                    </button>
                </span>
                <?php endif;?>

            <?php endif;?>


            <?php if(is_template_editable($templatename)):?>
                <?php if(Permission::model()->hasGlobalPermission('templates','update')):?>
                    <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <span class="glyphicon glyphicon-pencil  text-success"></span>
                        <?php eT("Rename"); ?>
                    </a>
                    <?php endif;?>

                <?php if(Permission::model()->hasGlobalPermission('templates','delete')):?>
                    <a class="btn btn-default" href="#" role="button" onclick='if (confirm("<?php eT("Are you sure you want to delete this template?", "js"); ?>")) window.open("<?php echo $this->createUrl('admin/templates/sa/delete/templatename/'.$templatename); ?>", "_top")'>
                        <span class="glyphicon glyphicon-trash  text-warning"></span>
                        <?php eT("Delete"); ?>
                    </a>
                    <?php endif;?>
                <?php endif;?>
        </div>

        <!-- Right Menu -->
        <div class="col-md-7 text-right form-inline">
            <div class="form-group">
                <label for='templatedir'><?php eT("Template:"); ?></label>
                <select class="listboxtemplates form-control" id='templatedir' name='templatedir' onchange="javascript: var uri = new Uri('<?php
                    // Don't put 'sa' into the URL dirctly because YIi will then try to use filenames directly in the path because of the route
                    echo $this->createUrl("admin/templates",array('sa'=>'view','editfile'=>$editfile,'screenname'=>$screenname)); ?>'); uri.addQueryParam('templatename',this.value); window.open(uri.toString(), '_top')">
                    <?php echo templateoptions($templates, $templatename); ?>
                </select>
            </div>

            <div class="form-group">
                <label for='listboxtemplates'><?php eT("Screen:"); ?></label>
                <?php echo CHtml::dropDownList('screenname',$screenname,$screens,array(
                    'id'=>'listboxtemplates',
                    'class'=>"listboxtemplates form-control",
                    'onchange'=> "javascript:  var uri = new Uri('".$this->createUrl("admin/templates",array('sa'=>'view','editfile'=>$editfile,'templatename'=>$templatename))."'); uri.addQueryParam('screenname',this.value); window.open(uri.toString(), '_top')"
                )); ?>
            </div>

            <?php if(isset($fullpagebar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" ></span>
                    <?php eT("Save");?>
                </a>
                <?php endif;?>

            <!-- Close -->
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $fullpagebar['closebutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-close" ></span>
                    <?php eT("Close");?>
                </a>
                <?php endif;?>

            <?php if(isset($fullpagebar['returnbutton'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("/admin"); ?>" role="button">
                    <span class="glyphicon glyphicon-backward" ></span>
                    &nbsp;&nbsp;
                    <?php eT("Return to admin home"); ?>
                </a>
                <?php endif;?>
        </div>
    </div>
</div>

<?php if($importModal):?>
    <div class="modal fade" tabindex="-1" role="dialog" id="importModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php eT("Upload template file") ?></h4>
                </div>
                <?php echo CHtml::form(array('admin/templates/sa/upload'), 'post', array('id'=>'importtemplate', 'name'=>'importtemplate', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'.gT('Please select a file to import!', 'js').'");')); ?>
                <div class="modal-body">
                    <input type='hidden' name='lid' value='$lid' />
                    <input type='hidden' name='action' value='templateupload' />
                    <div  class="form-group">
                        <label for='the_file'><?php eT("Select template ZIP file:") ?></label>
                        <input id='the_file' name='the_file' type="file" accept='.zip' />
                        <?php printf(gT('(Maximum file size: %01.2f MB)'),getMaximumFileUploadSize()/1024/1024); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if (!function_exists("zip_open")) {?>
                        <?php eT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") ?>
                        <?php } else {?>
                        <input class="btn btn-success" type='button' value='<?php eT("Import") ?>' onclick='if (validatefilename(this.form,"<?php eT('Please select a file to import!', 'js') ?>")) { this.form.submit();}' />
                        <?php }?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
                </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php endif;?>

<div class="col-lg-12 templateeditor">
    <h3><?php eT("Template editor:"); ?> <i><?php echo $templatename; ?></i></h3>


    <?php if(!is_template_editable($templatename)):?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
            <span class="glyphicon glyphicon-info-sign" ></span>&nbsp;&nbsp;&nbsp;
            <strong>
                <?php eT('Note: This is a standard template.');?>
            </strong>
            <?php
            printf(gT('If you want to edit it %s please copy it first%s.'),"<a href='#' title=\"".gT("Copy Template")."\""
                ." onclick=\"javascript: copyprompt('".gT("Please enter the name for the copied template:")."', '".gT("copy_of_")."$templatename', '$templatename', 'copy')\">",'</a>');
            ?>
        </div>
        <?php endif;?>


</div>
