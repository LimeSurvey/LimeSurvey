<?php
/* @var AdminController $this */
/* @var string $codelanguage */
/* @var string $highlighter */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('themeEditor');
?>
<script type="text/javascript">
    var adminlanguage = '<?php echo $codelanguage; ?>';
    var highlighter = '<?php echo $highlighter; ?>';
</script>
<script type='text/javascript'>
    function copyprompt(text, defvalue, copydirectory, action) {
        if (newtemplatename = window.prompt(text, defvalue)) {
            window.LS.sendPost(
                '<?php echo $this->createUrl('admin/themes/sa/template'); ?>' + action,
                false,
                {'action': 'template' + action, 'newname': newtemplatename, 'copydir': copydirectory})
        }
    }

    $(document).ready(function () {
        $("#importtemplatefile").submit(function () {

            filename = $("#upload_file").val();
            if (filename == "") {
                return false; // False click
            }
            var allowedtypes = ',<?php echo Yii::app()->getConfig('allowedthemeuploads') . ',' . Yii::app()->getConfig('allowedthemeimageformats'); ?>,';
            var lastdotpos = -1;
            var ext = '';
            if ((lastdotpos = filename.lastIndexOf('.')) < 0) {
                alert('<?php eT('This file type is not allowed to be uploaded.', 'js'); ?>');
                return false;
            } else {
                ext = ',' + filename.substr(lastdotpos + 1) + ',';
                ext = ext.toLowerCase();
                if (allowedtypes.indexOf(ext) < 0) {
                    alert('<?php eT('This file type is not allowed to be uploaded.', 'js'); ?>');
                    return false;
                } else {
                    return true;
                }
            }
        });
    });
</script>

<?php
App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>

<!-- Template Editor Bar -->
<div class='menubar surveybar' id="templateeditorbar">
    <div class="container-fluid">
        <div class='row row-cols-auto justify-content-between'>
            <!-- Left Menu -->
            <div class="col">
                <?php $importModal = false; ?>
                <?php if (is_writable($tempdir)) : ?>
                    <!-- Export -->
                    <?php if (Permission::model()->hasGlobalPermission('templates', 'export') && class_exists('ZipArchive')) : ?>
                        <a class="btn btn-outline-secondary"
                           id="button-export"
                           href="<?php echo $this->createUrl('admin/themes/sa/templatezip/templatename/' . $templatename) ?>"
                           role="button">
                            <span class="ri-upload-fill text-success"></span>
                            <?php eT("Export"); ?>
                        </a>
                    <?php endif; ?>

                    <!-- Copy -->
                    <?php if (Permission::model()->hasGlobalPermission('templates', 'create')) : ?>
                        <?php if (is_writable($userthemerootdir)) : ?>
                            <a class="btn btn-outline-secondary"
                               id="button-extend-<?php echo $templatename; ?>"
                               href="#"
                               role="button"
                               onclick="javascript: copyprompt('<?php eT("Please enter the name for the new theme:"); ?>', '<?php echo gT("extends_") . "$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
                                <span class="ri-file-copy-line text-success"></span>
                                <?php eT("Extend"); ?>
                            </a>
                        <?php else : ?>
                            <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                  title="<?php eT("The theme upload directory doesn't exist or is not writable."); ?>" style="display: inline-block"
                                  data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                                <span class="ri-file-copy-line text-success"></span>
                                <?php eT("Copy"); ?>
                            </button>
                        </span>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else : ?>
                    <!-- All buttons disabled -->

                    <!-- import disabled -->
                    <?php

                    if (!class_exists('ZipArchive')) {
                        $sMessage = gT("You cannot upload themes because you do not have the required ZIP library installed in PHP.");
                    } else {
                        $sMessage = gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/themes in order to enable this option.");
                    }
                    if (Permission::model()->hasGlobalPermission('templates', 'import')) :?>
                        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>"
                              style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>">
                        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                            <span class="ri-upload-fill text-muted"></span>
                                <?php eT("Import"); ?>
                        </button>
                    </span>
                    <?php endif; ?>

                    <!-- export disabled -->
                    <?php if (Permission::model()->hasGlobalPermission('templates', 'export')) : ?>
                        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>"
                              style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>">
                    <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                        <span class="ri-upload-fill text-muted"></span>
                        <?php eT("Export"); ?>
                    </button>
                </span>
                    <?php endif; ?>

                    <!-- create disabled -->
                    <?php if (Permission::model()->hasGlobalPermission('templates', 'create')) : ?>
                        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>"
                              style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $sMessage; ?>">
                    <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                        <span class="ri-file-copy-line text-muted"></span>
                        <?php eT("Copy"); ?>
                    </button>
                </span>
                    <?php endif; ?>

                <?php endif; ?>


                <?php if (is_template_editable($templatename)) : ?>
                    <?php if (Permission::model()->hasGlobalPermission('templates', 'update')) : ?>
                        <a class="btn btn-outline-secondary"
                           id="button-rename-theme"
                           href="#"
                           role="button"
                           onclick="javascript: copyprompt('<?php eT("Rename this theme to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                            <span class="ri-pencil-fill  text-success"></span>
                            <?php eT("Rename"); ?>
                        </a>
                    <?php endif; ?>

                    <?php if (Permission::model()->hasGlobalPermission('templates', 'delete')) : ?>
                        <a
                            id="button-delete"
                            href="<?php echo Yii::app()->getController()->createUrl('admin/themes/sa/delete/'); ?>"
                            data-post='{ "templatename": "<?php echo $templatename; ?>" }'
                            data-text="<?php eT('Are you sure you want to delete this theme?'); ?>"
                            data-button-no="<?= gT('Cancel'); ?>"
                            data-button-yes="<?= gT('Delete'); ?>"
                            data-button-type="btn-danger"
                            title="<?php eT('Delete'); ?>"
                            class="btn btn-danger selector--ConfirmModal">
                            <span class="ri-delete-bin-fill"></span>
                            <?php eT('Delete'); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right Menu -->
            <div class="col">
            <div class="row row-cols-lg-auto gx-1 gy-0 text-end">
                <!-- Theme Select Box -->
                <label class="col col-form-label text-nowrap" for='templatedir'><?php eT("Theme:"); ?></label>
                <div class="col">
                    <select class="col listboxtemplates form-select" id='templatedir' name='templatedir'
                            onchange="javascript: var uri = new Uri('<?php
                            // Don't put 'sa' into the URL dirctly because Yii will then try to use filenames directly in the path because of the route
                            echo $this->createUrl("admin/themes",
                                [
                                    'sa'         => 'view',
                                    'editfile'   => $relativePathEditfile,
                                    'screenname' => $screenname
                                ]); ?>'); uri.addQueryParam('templatename',this.value); window.open(uri.toString(), '_top')">
                        <?php echo themeoptions($templates, $templatename); ?>
                    </select>
                </div>

                <!-- Screen Select Box -->
                <label class="col col-form-label text-nowrap" for='listboxtemplates'><?php eT("Screen:"); ?></label>
                <div>
                    <?php echo CHtml::dropDownList('screenname',
                        $screenname,
                        $screens,
                        [
                            'id'       => 'listboxtemplates',
                            'class'    => "col listboxtemplates form-select",
                            'onchange' => "javascript:  var uri = new Uri('" . $this->createUrl("admin/themes",
                                    [
                                        'sa'           => 'view',
                                        'editfile'     => $relativePathEditfile,
                                        'templatename' => $templatename
                                    ]) . "'); uri.addQueryParam('screenname',this.value); window.open(uri.toString(), '_top')"
                        ]); ?>
                </div>

                <?php if (isset($fullpagebar['savebutton']['form'])) : ?>
                    <a class="btn btn-success" href="#" role="button" id="save-form-button"
                       data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                        <span class="ri-save-3-fill"></span>
                        <?php eT("Save"); ?>
                    </a>
                <?php endif; ?>

                <!-- Close -->
                <?php if (isset($fullpagebar['closebutton']['url'])) : ?>
                    <a class="btn btn-danger text-nowrap" href="<?php echo $fullpagebar['closebutton']['url']; ?>" role="button">
                        <span class="ri-close-fill"></span>
                        <?php eT("Close"); ?>
                    </a>
                <?php endif; ?>

                <!-- Return to Theme List -->
                <?php if (isset($templateEditorBar['buttons']['returnbutton'])) : ?>
                    <a class="btn btn-outline-secondary text-nowrap" href="<?php echo $this->createUrl("themeOptions/index"); ?>" role="button">
                        <span class="ri-rewind-fill"></span>
                        &nbsp;&nbsp;
                        <?php eT("Back"); ?>
                    </a>
                <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<?php if ($importModal) : ?>
    <?php $this->renderPartial('themeOptions/import_modal', ['importTemplate' => 'importtemplate', 'importModal' => 'importModal']); ?>
<?php endif; ?>

<!-- Template Editor -->
<div class="col-12 templateeditor">

    <?php if (!is_template_editable($templatename)) : ?>
        <?php
        $message = '<strong>' .
            gT('Note: This is a standard theme.') .
            '</strong> ' .
            sprintf(
                gT('If you want to modify it %s you can extend it%s.'),
                "<a href='#' title=\"" . gT("Extend theme") . "\""
                . " onclick=\"javascript: copyprompt('" . gT("Please enter the name for the new theme:") . "', '" . gT("extends_") . "$templatename', '$templatename', 'copy')\">",
                '</a>'
            );
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $message,
            'type' => 'info',
        ]);
        ?>
    <?php endif; ?>
    <?php if ((int)$templateapiversion < (int)App()->getConfig("templateapiversion")) : ?>
        <?php
        $message = sprintf(
            gT(
                'We can not guarantee optimum operation. It would be preferable to no longer use it or to make it compatible with the version %s of the LimeSurvey API.'
            ),
            intval(App()->getConfig("versionnumber"))
        );
        $this->widget('ext.AlertWidget.AlertWidget', [
            'header' => gT('This theme is out of date.'),
            'text' => $message,
            'type' => 'info',
        ]);
        ?>
    <?php endif; ?>
</div>
