<?php
Yii::app()->getClientScript()->registerPackage('jquery-ace'); 
Yii::app()->getClientScript()->registerScript('editorfiletype',"editorfiletype ='".$sEditorFileType."';",CClientScript::POS_HEAD);
?>

<div class="container-fluid">

  <?php if (is_template_editable($templatename)==true) { ?>
    <div class="row">
        <div class="col-12">
            <div class="h4">
                <?php echo sprintf(gT("Viewing file '%s'"), $filedisplayname); ?>
            </div>

            <?php if (!is_writable($templates[$templatename])) { ?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php eT("You can't save changes because the theme directory is not writable."); ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="row">

    <!-- Left column -->
    <div class="col-9 templateeditor">
        <?=CHtml::form(['admin/themes/sa/templatesavechanges'], 'POST', ['id'=>'editTemplate', 'name'=>'editTemplate'])?>
            <?php
                echo CHtml::hiddenField('templatename', $templatename, array('class'=>'templatename'));
                echo CHtml::hiddenField('screenname', $screenname, array('class'=>'screenname'));
                echo CHtml::hiddenField('editfile', $editfile);
                echo CHtml::hiddenField('relativePathEditfile', $relativePathEditfile);
                echo CHtml::hiddenField('action', 'templatesavechanges');
                echo CHtml::textArea('changes',
                !empty($editfile) ? file_get_contents($editfile) : '',
                array('rows'=>'20',
                    'cols'=>'40',
                    'data-filetype'=>$sEditorFileType,
                    'class'=>'ace '.$sTemplateEditorMode,
                    'style'=>'width:100%'
                ));
            ?>
            <p class='text-center'>
                <br/>
                <?php if (Permission::model()->hasGlobalPermission('templates','update')) {
                    $buttonType = $oEditedTemplate->getTemplateForFile($relativePathEditfile, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName
                        ? "savebutton" : "copybutton";
                    if (is_writable($templates[$templatename])):
                        if ($buttonType == "savebutton"): ?>
                            <button type='submit' class='btn btn-primary' id='button-save-changes' value='' <?=(!is_template_editable($templatename) ? "disabled='disabled' alt='".gT( "Changes cannot be saved to a standard theme."). "'" : "")?> />
                                <i class="fa fa-check"></i>
                                <?= gT('Save'); ?>
                            </button>
                        <?php else: ?>
                            <button type='submit' class='btn btn-default' id='button-save-changes' value='' <?=(!is_template_editable($templatename) ? "disabled='disabled' alt='".gT( "Changes cannot be saved to a standard theme."). "'" : "")?> />
                                <?= gT("Copy to local theme and save changes") ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php } ?>
            </p>
        </form>
        <br/>
        <div class="h4">
            <?php eT("Preview:"); ?>
        </div>
        <div class="jumbotron message-box">
            <input type='button' value='<?php eT("Mobile"); ?>' id='iphone' class="btn btn-default" />
            <input type='button' value='640x480' id='x640' class="btn btn-default" />
            <input type='button' value='800x600' id='x800' class="btn btn-default" />
            <input type='button' value='1024x768' id='x1024' class="btn btn-default" />
            <input type='button' value='<?php eT("Full"); ?>' id='full' class="btn btn-default" />
            <br>
            <br>
            <br>
            <br>

            <div style='width:90%; margin:0 auto;'>
                <?php if(isset($filenotwritten) && $filenotwritten==true) { ?>
                    <p>
                        <span class='errortitle'><?php echo sprintf(gT("Please change the directory permissions of the folder %s in order to preview themes."), $tempdir); ?></span>
                    </p>
                <?php } else { ?>
                    <p>
                        <iframe id='previewiframe' title='Preview' src='<?php echo $this->createUrl('admin/themes/sa/tmp/',array('id'=>$time)); ?>' height='768' name='previewiframe' style='width:95%;background-color: white;'>Embedded Frame</iframe>
                    </p>
                <?php } ?>
            </div>
        </div>
    </div>

        <!-- Right column -->
        <div class="col-3" id='templateleft'>
            <div class="card mb-1">
                <div class="col-12 card-body">
                    <label class="card-title"><?php eT("Screen part files:"); ?></label>
                <?php foreach ($files as $file) { ?>
                    <div class="row">
                        <div class="col-md-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate,false) && $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) { ?>
                                <?php if (Permission::model()->hasGlobalPermission('templates','delete')) { ?>
                                    <?=CHtml::form(array('admin/themes','sa'=>'templatefiledelete'), 'post', ['class' => 'd-grid gap-2']); ?>
                                        <input type='hidden' name="filetype" value="<?php echo CHtml::encode('screen'); ?>" />
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>" />
                                        <input type='submit' class='btn btn-default btn-xs' value='<?php eT("Reset"); ?>' onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ", "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>' />
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>' />
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>' />
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="badge bg-danger w-100"> <?php eT("Inherited"); ?> </span>
                            <?php }?>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
            <div class="card mb-1">
                <div class="col-12 card-body">
                    <label class="card-title"><?php eT("JavaScript files:"); ?></label>

                <?php foreach ($jsfiles as $file) { ?>
                    <div class="row">
                        <div class="col-md-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) {?>
                                <?php if (Permission::model()->hasGlobalPermission('templates','delete')) { ?>
                                    <?=CHtml::form(array('admin/themes','sa'=>'templatefiledelete'), 'post', ['class' => 'd-grid gap-2']); ?>
                                    <input type='hidden' name="filetype" value="<?php echo CHtml::encode('js'); ?>" />
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>" />
                                        <input type='submit' class='btn btn-default btn-xs template-files-delete-button' value='<?php eT("Reset"); ?>' onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ", "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>' />
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>' />
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>' />
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="badge bg-danger w-100"><?php eT("Inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>

        <div class="card mb-1">
            <div class="col-12 card-body">
                <label class="card-title"><?php eT("CSS files:"); ?></label>
                <?php foreach ($cssfiles as $file) { ?>
                    <div class="row">
                        <div class="col-md-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) { ?>
                                <?php if (Permission::model()->hasGlobalPermission('templates','delete')) { ?>
                                    <?=CHtml::form(array('admin/themes','sa'=>'templatefiledelete'), 'post', ['class' => 'd-grid gap-2']); ?>
                                    <input type='hidden' name="filetype" value="<?php echo CHtml::encode('css'); ?>" />
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>" />
                                        <input type='submit' class='btn btn-default btn-xs template-files-delete-button' value='<?php eT("Reset"); ?>' onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ", "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>' />
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>' />
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>' />
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="badge bg-danger w-100"><?php eT("Inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>

    <div class="card mb-1">
    <div class="col-12 card-body" style="">
            <label class="card-title"><?php eT("Other files:"); ?></label>
            <div class="col-12 mb-3 other-files-list">
                <?php foreach ($otherfiles as $fileName => $file) { ?>
                    <div class="row other-files-row">
                        <div class="col-md-9 other-files-filename">
                            <?php echo CHtml::encode($fileName) ;?>
                        </div>
                        <div class="col-md-3">
                            <?php //TODO: make it ajax and less messy ?>
                            <?php if ( $oEditedTemplate->getTemplateForFile($fileName, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) {
                                if (Permission::model()->hasGlobalPermission('templates','delete')) { ?>
                                    <?=CHtml::form(array('admin/themes','sa'=>'templatefiledelete'), 'post', ['class' => 'd-grid gap-2']); ?>
                                        <input type='hidden' name="filetype" value="<?php echo CHtml::encode('other'); ?>" />
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>" />
                                        <input type='submit' class='btn btn-default btn-xs template-files-delete-button other-files-delete-button' value='<?php eT("Reset"); ?>' onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ", "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>' />
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>' />
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>' />
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="badge bg-danger w-100"><?php eT("Inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
        </div>
        <div class="mb-3">
            <?php if (Permission::model()->hasGlobalPermission('templates','update')) { ?>
                <div class="">
                    <?php echo CHtml::form(array('admin/themes/sa/uploadfile'), 'post', array('id'=>'importtemplatefile', 'class' => 'row', 'name'=>'importtemplatefile', 'enctype'=>'multipart/form-data')); ?>
                        <label class="form-label col-12">
                            <?php printf(gT("Upload a file (maximum size: %d MB):"), getMaximumFileUploadSize()/1024/1024); ?>
                        </label>
                        <div class="col-8">
                            <input name='upload_file' id="upload_file" type="file" class="form-control" required="required" />
                            <input type='hidden' name='editfile' value='<?php echo htmlspecialchars($relativePathEditfile); ?>' />
                            <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                            <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                            <input type='hidden' name='action' value='templateuploadfile' />
                        </div>
                        <div class="col-4">
                            <button type='submit' class='btn btn-default text-nowrap' <?=(!is_template_editable($templatename) ? "disabled='disabled'" : '') ?>>
                                <i class="icon-import"></i>
                                <?= gT("Upload"); ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php } ?>
        </div>

        <div class="">
            <a href="#" data-bs-toggle="modal" data-bs-target="#fileHelp" />
                <?php eT('Tip: How to embed a picture in your theme?'); ?>
            </a>
        </div>
    </div>
    </div>


    </div>

    <!-- Modal -->
    <div class="modal fade" id="fileHelp" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel"><?php eT('Tip: How to display a picture in your theme?'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php eT('To use a picture in a .twig file:');?>
                    <br/>
                    <code> {{ image('./files/myfile.png', 'alt-text for my file', {"class": "myclass"}) }}</code>
                    <br/>
                    <br/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                        <?php eT("Close");?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php }?>


</div>
