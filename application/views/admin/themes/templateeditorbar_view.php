<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('themeEditor');
?>
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
            window.LS.sendPost(
                '<?php echo $this->createUrl('admin/themes/sa/template'); ?>'+action,
                false,
                {'action' : 'template'+action,'newname' : newtemplatename,'copydir' : copydirectory})
        }
    }

    $(document).ready(function(){
        $("#importtemplatefile").submit(function(){

            filename = $("#upload_file").val();
            if(filename==""){
                return false; // False click
            }
            var allowedtypes=',<?php echo Yii::app()->getConfig('allowedthemeuploads'); ?>,';
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

            <!-- Import -->
            <?php $importModal=false;?>
            <?php if(is_writable($tempdir)):?>
                <?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
                    <?php if (is_writable($userthemerootdir) && function_exists("zip_open")):?>
                        <?php $importModal=true;?>
                        <a class="btn btn-default" id="button-import" href="" role="button" data-toggle="modal" data-target="#importModal">
                            <span class="icon-import text-success"></span>
                            <?php eT("Import"); ?>
                        </a>
                        <?php else:
                        if (function_exists("zip_open")){
                            $sMessage=gT("The theme upload directory doesn't exist or is not writable.");
                        }
                        else{
                            $sMessage=gT("You do not have the required ZIP library installed in PHP.");
                        }
                        ?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $sMessage; ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                            <button type="button" id="button-import" class="btn btn-default btntooltip" disabled="disabled">
                                <span class="icon-import text-success"></span>
                                <?php eT("Import"); ?>
                            </button>
                        </span>
                        <?php endif;?>
                    <?php else: ?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("We are sorry but you don't have permissions to do this."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                        <button type="button" id="button-import" class="btn btn-default btntooltip" disabled="disabled">
                            <span class="icon-import text-success"></span>
                            <?php eT("Import"); ?>
                        </button>
                    </span>
                    <?php endif;?>

                <!-- Export -->
                <?php if(Permission::model()->hasGlobalPermission('templates','export') && function_exists("zip_open")):?>
                    <a class="btn btn-default" id="button-export" href="<?php echo $this->createUrl('admin/themes/sa/templatezip/templatename/' . $templatename) ?>" role="button">
                        <span class="icon-export text-success"></span>
                        <?php eT("Export"); ?>
                    </a>
                    <?php endif;?>

                <!-- Copy -->
                <?php if(Permission::model()->hasGlobalPermission('templates','create')):?>
                    <?php if (is_writable($userthemerootdir)):?>
                        <a class="btn btn-default" id="button-extend-<?php echo $templatename; ?>" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Please enter the name for the new theme:"); ?>', '<?php echo gT("extends_")."$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
                            <span class="icon-copy text-success"></span>
                            <?php eT("Extend"); ?>
                        </a>
                        <?php else: ?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("The theme upload directory doesn't exist or is not writable."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
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
                    $sMessage=gT("You cannot upload themes because you do not have the required ZIP library installed in PHP.");
                }
                else
                {
                    $sMessage=gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/themes in order to enable this option.");
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
                    <a class="btn btn-default" id="button-rename-theme" href="#" role="button" onclick="javascript: copyprompt('<?php eT("Rename this theme to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <span class="fa fa-pencil  text-success"></span>
                        <?php eT("Rename"); ?>
                    </a>
                    <?php endif;?>

                <?php if(Permission::model()->hasGlobalPermission('templates','delete')):?>
                    <a
                        id="button-delete"
                        href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/delete/'); ?>"
                        data-post='{ "templatename": "<?php echo $templatename; ?>" }'
                        data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                        title="<?php eT('Delete'); ?>"
                        class="btn btn-danger selector--ConfirmModal">
                            <span class="fa fa-trash "></span>
                            <?php eT('Delete'); ?>
                        </a>
                    <?php endif;?>
                <?php endif;?>
        </div>

        <!-- Right Menu -->
        <div class="col-md-7 text-right form-inline">
            <div class="form-group">
                <label for='templatedir'><?php eT("Theme:"); ?></label>
                <select class="listboxtemplates form-control" id='templatedir' name='templatedir' onchange="javascript: var uri = new Uri('<?php
                    // Don't put 'sa' into the URL dirctly because YIi will then try to use filenames directly in the path because of the route
                    echo $this->createUrl("admin/themes",array('sa'=>'view','editfile'=>$relativePathEditfile,'screenname'=>$screenname)); ?>'); uri.addQueryParam('templatename',this.value); window.open(uri.toString(), '_top')">
                    <?php echo themeoptions($templates, $templatename); ?>
                </select>
            </div>

            <div class="form-group">
                <label for='listboxtemplates'><?php eT("Screen:"); ?></label>
                <?php echo CHtml::dropDownList('screenname',$screenname,$screens,array(
                    'id'=>'listboxtemplates',
                    'class'=>"listboxtemplates form-control",
                    'onchange'=> "javascript:  var uri = new Uri('".$this->createUrl("admin/themes",array('sa'=>'view','editfile'=>$relativePathEditfile,'templatename'=>$templatename))."'); uri.addQueryParam('screenname',this.value); window.open(uri.toString(), '_top')"
                )); ?>
            </div>

            <?php if(isset($fullpagebar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                    <span class="fa fa-floppy-o" ></span>
                    <?php eT("Save");?>
                </a>
                <?php endif;?>

            <!-- Close -->
            <?php if(isset($fullpagebar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $fullpagebar['closebutton']['url']; ?>" role="button">
                    <span class="fa fa-close" ></span>
                    <?php eT("Close");?>
                </a>
                <?php endif;?>

            <?php if(isset($fullpagebar['returnbutton'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/themeoptions"); ?>" role="button">
                    <span class="fa fa-backward" ></span>
                    &nbsp;&nbsp;
                    <?php eT("Return to theme list"); ?>
                </a>
                <?php endif;?>
        </div>
    </div>
</div>

<?php if($importModal):?>
    <?php $this->renderPartial('themeoptions/import_modal',[]); ?>
<?php endif;?>

<div class="col-lg-12 templateeditor">
    <div class="h3 theme-editor-header"><?php eT("Theme editor:"); ?> <i><?php echo $templatename; ?></i></div>

    <?php if(!is_template_editable($templatename)):?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
            <span class="fa fa-info-sign" ></span>&nbsp;&nbsp;&nbsp;
            <strong>
                <?php eT('Note: This is a standard theme.');?>
            </strong>
            <?php
            printf(gT('If you want to modify it %s you can extend it%s.'),"<a href='#' title=\"".gT("Extend theme")."\""
                ." onclick=\"javascript: copyprompt('".gT("Please enter the name for the new theme:")."', '".gT("extends_")."$templatename', '$templatename', 'copy')\">",'</a>');
            ?>
        </div>
    <?php endif;?>
    <?php if(intval($templateapiversion) < intval(App()->getConfig("versionnumber")) ):?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
            <div class="h4">
                <span class="fa fa-info-sign" ></span>
                <?php eT('This theme is out of date.');?>
            </div>
            <?php
                printf(gT("We can not guarantee optimum operation. It would be preferable to no longer use it or to make it compatible with the version %s of the LimeSurvey API."),intval(App()->getConfig("versionnumber")));
            ?>
        </div>
    <?php endif;?>
</div>
