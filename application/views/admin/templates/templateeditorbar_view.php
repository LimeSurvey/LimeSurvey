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
                <?php if(is_writable($usertemplaterootdir)):?>
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
            <?php if(is_writable($tempdir) && function_exists("zip_open")):?>
                <?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <?php if (is_writable($usertemplaterootdir)):?>
                        <a class="btn btn-default" href="<?php echo $this->createUrl('admin/templates/sa/upload'); ?>" role="button">
                            <span class="icon-import text-success"></span>
                            <?php eT("Import"); ?>
                        </a>
                    <?php else: ?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("The template upload directory doesn't exist or is not writable."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
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
                <?php if(Permission::model()->hasGlobalPermission('templates','export')):?>
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
                <?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-import text-muted"></span>
                            <?php eT("Import"); ?>
                        </button>
                    </span>
                <?php endif;?>

                <!-- export disabled -->
                <?php if(Permission::model()->hasGlobalPermission('templates','export')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-export text-muted"></span>
                            <?php eT("Export"); ?>
                        </button>
                    </span>
                <?php endif;?>

                <!-- create disabled -->
                <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
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
                <select class="listboxtemplates form-control" id='templatedir' name='templatedir' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/view/editfile/".$editfile."/screenname/".$screenname); ?>/templatename/'+escape(this.value), '_top')">
                    <?php echo templateoptions($templates, $templatename); ?>
                </select>
            </div>

            <div class="form-group">
                <label for='listboxtemplates'><?php eT("Screen:"); ?></label>
                <select class="listboxtemplates form-control" id='listboxtemplates' name='screenname' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/screenredirect/editfile/".$editfile."/templatename/".$templatename); ?>/screenname/'+escape(this.value), '_top')">
                    <?php echo makeoptions($screens, "id", "name", HTMLEscape($screenname) ); ?>
                </select>
            </div>

            <?php if(isset($fullpagebar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" ></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>

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
                    <?php eT("Return to admin panel"); ?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>


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
