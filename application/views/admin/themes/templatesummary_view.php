<?php
Yii::app()->getClientScript()->registerPackage('jquery-ace'); 
Yii::app()->getClientScript()->registerScript('editorfiletype',"editorfiletype ='".$sEditorFileType."';",CClientScript::POS_HEAD);
?>


  <?php if (is_template_editable($templatename)==true) { ?>
    <div class="row template-sum">
        <div class="col-lg-12">
            <div class="h4">
                <?php echo sprintf(gT("Viewing file '%s'"),$editfile); ?>
            </div>

            <?php if (!is_writable($templates[$templatename])) { ?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php eT("You can't save changes because the theme directory is not writable."); ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="row template-sum">
        <div class="col-sm-2" id='templateleft'>
            <div>
                <?php eT("Screen part files:"); ?>
                <div class="col-sm-12 well" style="padding-left: 0;">
                <?php foreach ($files as $file) { ?>
                    <div class="row">
                        <div class="col-sm-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-sm-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate,false) && $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) { ?>
                                <span class="label label-success"> <?php eT("local"); ?> </span>
                            <?php } else { ?>
                                <span class="label label-danger"> <?php eT("inherited"); ?> </span>
                            <?php }?>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
            <div>
                <?php eT("JavaScript files:"); ?>
                <div class="col-sm-12 well">

                <?php foreach ($jsfiles as $file) { ?>
                    <div class="row">
                        <div class="col-sm-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-sm-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) {?>
                                <span class="label label-success"><?php eT("local"); ?></span>
                            <?php } else { ?>
                                <span class="label label-danger"><?php eT("inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
        <div>
            <?php eT("CSS files:"); ?>
            <div class="col-sm-12 well">
                <?php foreach ($cssfiles as $file) { ?>
                    <div class="row">
                        <div class="col-sm-9">
                            <a href="<?php echo $this->createUrl('admin/themes', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>" class="<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>">
                                <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                            </a>
                        </div>
                        <div class="col-sm-3">
                            <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) { ?>
                                <span class="label label-success"><?php eT("local"); ?></span>
                            <?php } else { ?>
                                <span class="label label-danger"><?php eT("inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>

    <div class="col-lg-8 templateeditor">
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
                    $sSaveText = ( $oEditedTemplate->getTemplateForFile($relativePathEditfile, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName)
                        ? gT("Save changes")
                        : gT("Copy to local theme and save changes");
                    if (is_writable($templates[$templatename])) { ?>
                        <input type='submit' class='btn btn-default' id='button-save-changes' value='<?php echo $sSaveText; ?>' <?=(!is_template_editable($templatename) ? "disabled='disabled' alt='".gT( "Changes cannot be saved to a standard theme."). "'" : "")?> />
                    <?php } ?>
                <?php } ?>
            </p>
        </form>
    </div>
    <div class="col-lg-2" style="overflow-x: hidden">
        <div>
            <?php eT("Other files:"); ?>
            <br/>
            <div class="col-sm-12 well other-files-list">
                <?php foreach ($otherfiles as $fileName => $file) { ?>
                    <div class="row other-files-row">
                        <div class="col-sm-9 other-files-filename">
                            <?php echo CHtml::encode($fileName) ;?>
                        </div>
                        <div class="col-sm-3">
                            <?php //TODO: make it ajax and less messy ?>
                            <?php if ( $oEditedTemplate->getTemplateForFile($fileName, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) {
                                if (Permission::model()->hasGlobalPermission('templates','delete')) { ?>
                                    <?=CHtml::form(array('admin/themes','sa'=>'templatefiledelete'), 'post'); ?>
                                        <input type='hidden' name="otherfile" value="<?php echo CHtml::encode($fileName); ?>" />
                                        <input type='submit' class='btn btn-default btn-xs other-files-delete-button' value='<?php eT("Delete"); ?>' onclick="javascript:return confirm('<?php eT(" Are you sure you want to delete this file? ", "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>' />
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>' />
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>' />
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="label label-danger"><?php eT("inherited"); ?></span>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>
            </div>
            <br>
        </div>
        <div style='margin-top:1em;'>
            <?php if (Permission::model()->hasGlobalPermission('templates','update')) { ?>
                <?php echo CHtml::form(array('admin/themes/sa/uploadfile'), 'post', array('id'=>'importtemplatefile', 'name'=>'importtemplatefile', 'enctype'=>'multipart/form-data')); ?>
                    <?php printf(gT("Upload a file (maximum size: %d MB):"),getMaximumFileUploadSize()/1024/1024); ?>
                    <br>
                    <input name='upload_file' id="upload_file" type="file" required="required" />
                    <input type='submit' value='<?php eT("Upload"); ?>' class='btn btn-default' <?=(!is_template_editable($templatename) ? "disabled='disabled'" : '') ?> />
                    <input type='hidden' name='editfile' value='<?php echo htmlspecialchars($relativePathEditfile); ?>' />
                    <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                    <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                    <input type='hidden' name='action' value='templateuploadfile' />
                </form>
            <?php } ?>
        </div>

        <div class="">
            <a href="#" data-toggle="modal" data-target="#fileHelp" />
                <?php eT('Tip: How to embed a picture in your theme?'); ?>
            </a>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fileHelp" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h4" id="myModalLabel">
                        <?php eT('Tip: How to display a picture in your theme?'); ?>
                    </div>
                </div>
                <div class="modal-body">
                    <?php eT('To use a picture in a .twig file:');?>
                    <br/>
                    <code> {{ image('./files/myfile.png', 'alt-text for my file', {"class": "myclass"}) }}</code>
                    <br/>
                    <br/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php eT("Close");?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php }?>
<div class="row template-sum" style="margin-bottom: 100px;">
    <div class="col-lg-12">
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
    </div>
