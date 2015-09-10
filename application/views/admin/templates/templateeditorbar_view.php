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
        <div class="col-md-8">

            <?php if(is_writable($tempdir) && is_writable($usertemplaterootdir)):?>
                <!-- Create -->
                <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Create new template called:"); ?>', '<?php eT("NewTemplate"); ?>', 'default', 'copy')">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png" />
                    <?php eT("Create new template"); ?>
                </a>
            <?php endif;?>
            
            <?php if(is_writable($tempdir) && is_writable($usertemplaterootdir)):?>
                
                <!-- Import -->
                <?php if(Permission::model()->hasGlobalPermission('templates','import') && function_exists("zip_open")):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/templates/sa/upload'); ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/import.png" />
                        <?php eT("Import template"); ?>
                    </a>
                <?php else: ?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("We are sorry but you don't have permissions to do this."); eT(" Or: "); eT("zip library not supported by PHP, Import ZIP Disabled"); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/import.png" />
                            <?php eT("Import template"); ?>
                        </button>
                    </span>
                <?php endif;?>
                
                <!-- Export -->
                <?php if(Permission::model()->hasGlobalPermission('templates','export')):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/templates/sa/templatezip/templatename/' . $templatename) ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/export.png" />
                        <?php eT("Export Template"); ?>
                    </a>
               <?php endif;?>
                
               <!-- Copy -->
               <?php if(Permission::model()->hasGlobalPermission('templates','create')):?> 
                    <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Please enter the name for the copied template:"); ?>', '<?php echo gT("copy_of_")."$templatename"; ?>',            '<?php echo $templatename; ?>', 'copy')">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/copy.png" />
                        <?php eT("Copy Template"); ?>
                    </a>
               <?php endif;?>
               
            <?php else: ?>

                <!-- All buttons disabled -->
                
                <!-- import disabled -->
                <?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/import_disabled.png" />
                            <?php eT("Import template"); ?>
                        </button>
                    </span>                    
                <?php endif;?>     

                <!-- export disabled -->
                <?php if(Permission::model()->hasGlobalPermission('templates','export')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/export_disabled.png" />
                            <?php eT("Export template"); ?>
                        </button>
                    </span>                    
                <?php endif;?>

                <!-- create disabled -->
                <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>"  style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/copy_disabled.png" />
                            <?php eT("Copy template"); ?>
                        </button>
                    </span>                    
                <?php endif;?>                

            <?php endif;?>


            <?php if(is_template_editable($templatename)):?>
                <?php if(Permission::model()->hasGlobalPermission('templates','update')):?>
                    <a class="btn btn-default" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/edit.png" />
                        <?php eT("Rename this template"); ?>
                    </a>                                    
                <?php endif;?>
                
                <?php if(Permission::model()->hasGlobalPermission('templates','delete')):?>
                    <a class="btn btn-default" href="#" role="button" onclick='if (confirm("<?php eT("Are you sure you want to delete this template?", "js"); ?>")) window.open("<?php echo $this->createUrl('admin/templates/sa/delete/templatename/'.$templatename); ?>", "_top")'>
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
                        <?php eT("Delete this template"); ?>
                    </a>                                                        
                <?php endif;?>                    
            <?php endif;?>
        </div>


        <!-- Menu Right -->
            
        <div class="col-md-4 text-right form-inline">
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
                <a class="btn btn-success" href="#" role="button" id="save-form-button" aria-data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>
            
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $this->createUrl($fullpagebar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
            <?php if(isset($fullpagebar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("/admin"); ?>" role="button">
                    <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
                    &nbsp;&nbsp;
                    <?php eT("Return to survey administration."); ?>
                </a>
            <?php endif;?>            
        </div>
    </div>
</div>


<div class="col-lg-12 list-surveys">
    <h3><?php eT("Template:"); ?> <i><?php echo $templatename; ?></i></h3>


<?php if(!is_template_editable($templatename)):?>
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;
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