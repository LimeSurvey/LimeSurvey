<?php
Yii::app()->getClientScript()->registerPackage('jquery-ace');
Yii::app()->getClientScript()->registerScript('editorfiletype', "editorfiletype ='" . $sEditorFileType . "';", CClientScript::POS_HEAD);
?>


<?php if (is_template_editable($templatename) == true) { ?>
    <div class="row">
        <div class="col-12">
            <div class="h1">
                <?php echo sprintf(gT("Viewing file '%s'"), $filedisplayname); ?>
            </div>

            <?php if (!is_writable($templates[$templatename])) { ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("You can't save changes because the theme directory is not writable."),
                    'type' => 'warning',
                ]);
                ?>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <!-- Left column -->
        <div class="col-9 templateeditor">
            <?= CHtml::form(['admin/themes/sa/templatesavechanges'], 'POST', ['id' => 'editTemplate', 'name' => 'editTemplate']) ?>
            <?php
            echo CHtml::hiddenField('templatename', $templatename, ['class' => 'templatename']);
            echo CHtml::hiddenField('screenname', $screenname, ['class' => 'screenname']);
            echo CHtml::hiddenField('editfile', $editfile);
            echo CHtml::hiddenField('relativePathEditfile', $relativePathEditfile);
            echo CHtml::hiddenField('action', 'templatesavechanges');
            echo CHtml::textArea('changes',
                !empty($editfile) ? file_get_contents($editfile) : '',
                [
                    'rows'          => '20',
                    'cols'          => '40',
                    'data-filetype' => $sEditorFileType,
                    'class'         => 'ace ' . $sTemplateEditorMode,
                    'style'         => 'width:100%'
                ]);
            ?>
            <p class='text-center'>
                <br/>
                <?php if (Permission::model()->hasGlobalPermission('templates', 'update')) {
                    $buttonType = $oEditedTemplate->getTemplateForFile($relativePathEditfile,
                        $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName
                        ? "savebutton" : "copybutton";
                    if (is_writable($templates[$templatename])):
                        if ($buttonType === "savebutton"): ?>
                            <button type='submit' class='btn btn-primary' id='button-save-changes'
                                    value='' <?= (!is_template_editable($templatename) ? "disabled='disabled' alt='" . gT("Changes cannot be saved to a standard theme.") . "'" : "") ?> >
                                <i class="ri-check-fill"></i>
                                <?= gT('Save'); ?>
                            </button>
                        <?php else: ?>
                            <button type='submit' class='btn btn-outline-secondary' id='button-save-changes'
                                    value='' <?= (!is_template_editable($templatename) ? "disabled='disabled' alt='" . gT("Changes cannot be saved to a standard theme.") . "'" : "") ?> >
                                <?= gT("Copy to local theme and save changes") ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php } ?>
            </p>
            <?= CHtml::endForm() ?>
            <br/>
            <?= $this->renderPartial('/admin/themes/templateeditor_preview', ['time' => $time]); ?>
        </div>
        <!-- Right column -->
        <div class="col-3" id='templateleft'>
            <div class="card card-primary mb-3">
                <div class="col-12 card-body">
                    <label class="card-title"><?php eT("Screen part files:"); ?></label>
                    <?php foreach ($files as $file) { ?>
                        <div class="row">
                            <div class="col-8">
                                <a href="<?php echo $this->createUrl('admin/themes',
                                    ['sa' => 'view', 'screenname' => $screenname, 'templatename' => $templatename, 'editfile' => $file]); ?>"
                                   class="<?= $file == $relativePathEditfile ? 'text-danger' : 'text-success' ?>">
                                    <?= (empty(substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1)))
                                        ? $file
                                        : substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1) ?>
                                </a>
                            </div>
                            <div class="col-4">
                                <?php if ($oEditedTemplate->getTemplateForFile($file, $oEditedTemplate, false)
                                    && $oEditedTemplate->getTemplateForFile($file,
                                        $oEditedTemplate)->sTemplateName === $oEditedTemplate->sTemplateName) { ?>
                                    <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) { ?>
                                        <?= CHtml::form(['admin/themes', 'sa' => 'templatefiledelete'], 'post', ['class' => 'd-grid gap-2']); ?>
                                        <input type='hidden' name="filetype" value="<?php echo CHtml::encode('screen'); ?>"/>
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>"/>
                                        <input type='submit' class='btn btn-outline-secondary btn-xs' value='<?php eT("Reset"); ?>'
                                               onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ",
                                                   "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>'/>
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>'/>
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>'/>
                                        <?= CHtml::endForm() ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="badge bg-danger"> <?php eT("Inherited"); ?> </span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="card card-primary mb-3">
                <div class="col-12 card-body">
                    <label class="card-title"><?php eT("JavaScript files:"); ?></label>
                    <?php foreach ($jsfiles as $file) { ?>
                        <div class="row">
                            <div class="col-8">
                                <a href="<?php echo $this->createUrl('admin/themes',
                                    ['sa' => 'view', 'screenname' => $screenname, 'templatename' => $templatename, 'editfile' => $file]); ?>"
                                   class="<?= $file == $relativePathEditfile ? 'text-danger' : 'text-success' ?>">
                                    <?= (empty(substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1)))
                                        ? $file
                                        : substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1); ?>
                                </a>
                            </div>
                            <div class="col-4">
                                <?php if ($oEditedTemplate->getTemplateForFile($file,
                                        $oEditedTemplate)->sTemplateName === $oEditedTemplate->sTemplateName) { ?>
                                    <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) { ?>
                                        <?= CHtml::form(['admin/themes', 'sa' => 'templatefiledelete'], 'post', ['class' => 'd-grid gap-2']); ?>
                                        <input type='hidden' name="filetype" value="<?php echo CHtml::encode('js'); ?>"/>
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>"/>
                                        <input type='submit' class='btn btn-outline-secondary btn-xs template-files-delete-button'
                                               value='<?php eT("Reset"); ?>'
                                               onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ",
                                                   "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>'/>
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>'/>
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>'/>
                                        <?= CHtml::endForm() ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="badge bg-danger "><?php eT("Inherited"); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="card card-primary mb-3">
                <div class="col-12 card-body">
                    <label class="card-title"><?php eT("CSS files:"); ?></label>
                    <?php foreach ($cssfiles as $file) { ?>
                        <div class="row">
                            <div class="col-8">
                                <a href="<?php echo $this->createUrl('admin/themes',
                                    ['sa' => 'view', 'screenname' => $screenname, 'templatename' => $templatename, 'editfile' => $file]); ?>"
                                   class="<?= $file === $relativePathEditfile ? 'text-danger' : 'text-success'?>">
                                    <?= (empty(substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1)))
                                        ? $file
                                        : substr(strrchr((string) $file, DIRECTORY_SEPARATOR), 1); ?>
                                </a>
                            </div>
                            <div class="col-4">
                                <?php if ($oEditedTemplate->getTemplateForFile($file,
                                        $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) { ?>
                                    <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) { ?>
                                        <?= CHtml::form(['admin/themes', 'sa' => 'templatefiledelete'], 'post', ['class' => 'd-grid gap-2']); ?>
                                        <input type='hidden' name="filetype" value="<?php echo CHtml::encode('css'); ?>"/>
                                        <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>"/>
                                        <input type='submit' class='btn btn-outline-secondary btn-xs template-files-delete-button'
                                               value='<?php eT("Reset"); ?>'
                                               onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ",
                                                   "js"); ?>')"/>
                                        <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>'/>
                                        <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>'/>
                                        <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>'/>
                                        <?= CHtml::endForm() ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="badge bg-danger "><?php eT("Inherited"); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="card card-primary mb-3">
                <div class="col-12 card-body" style="">
                    <label class="card-title"><?php eT("Other files:"); ?></label>
                    <div class="col-12 mb-3 other-files-list">
                        <?php foreach ($otherfiles as $fileName => $file) { ?>
                            <div class="row other-files-row">
                                <div class="col-8 other-files-filename">
                                    <?php echo CHtml::encode($fileName); ?>
                                </div>
                                <div class="col-4">
                                    <?php //TODO: make it ajax and less messy ?>
                                    <?php if ($oEditedTemplate->getTemplateForFile($fileName,
                                            $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName) {
                                        if (Permission::model()->hasGlobalPermission('templates', 'delete')) { ?>
                                            <?= CHtml::form(['admin/themes', 'sa' => 'templatefiledelete'],
                                                'post',
                                                ['class' => 'd-grid gap-2']); ?>
                                            <input type='hidden' name="filetype" value="<?php echo CHtml::encode('other'); ?>"/>
                                            <input type='hidden' name="filename" value="<?php echo CHtml::encode($file); ?>"/>
                                            <input type='submit'
                                                   class='btn btn-outline-secondary btn-xs template-files-delete-button other-files-delete-button'
                                                   value='<?php eT("Reset"); ?>'
                                                   onclick="javascript:return confirm('<?php eT(" Are you sure you want to reset this file? ",
                                                       "js"); ?>')"/>
                                            <input type='hidden' name='screenname' value='<?php echo CHtml::encode($screenname); ?>'/>
                                            <input type='hidden' name='templatename' value='<?php echo CHtml::encode($templatename); ?>'/>
                                            <input type='hidden' name='editfile' value='<?php echo CHtml::encode($relativePathEditfile); ?>'/>
                                            <?= CHtml::endForm() ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="badge bg-danger "><?php eT("Inherited"); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="mb-3">
                        <?php if (Permission::model()->hasGlobalPermission('templates', 'update')) { ?>
                            <div class="">
                                <?php echo CHtml::form(['admin/themes/sa/uploadfile'],
                                    'post',
                                    [
                                        'id'      => 'importtemplatefile',
                                        'name'    => 'importtemplatefile',
                                        'enctype' => 'multipart/form-data'
                                    ]); ?>
                                <div class="row">
                                    <label class="form-label col-12">
                                        <?php printf(gT("Upload a file (maximum size: %d MB):"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <input name='upload_file' id="upload_file" type="file" class="form-control" required="required"/>
                                        <input type='hidden' name='editfile' value='<?php echo htmlspecialchars((string) $relativePathEditfile); ?>'/>
                                        <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>'/>
                                        <input type='hidden' name='templatename' value='<?php echo $templatename; ?>'/>
                                        <input type='hidden' name='action' value='templateuploadfile'/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type='submit'
                                                class='btn btn-outline-secondary text-nowrap' <?= (!is_template_editable($templatename) ? "disabled='disabled'" : '') ?>>
                                            <i class="ri-upload-fill"></i>
                                            <?= gT("Upload"); ?>
                                        </button>
                                    </div>
                                </div>
                                <?= CHtml::endForm() ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#fileHelp">
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
                        <?php eT('To use a picture in a .twig file:'); ?>
                        <br/>
                        <code> {{ image('./files/myfile.png', 'alt-text for my file', {"class": "myclass"}) }}</code>
                        <br/>
                        <br/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <?php eT("Close"); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <?= $this->renderPartial('/admin/themes/templateeditor_preview', ['time' => $time]); ?>
<?php } ?>
