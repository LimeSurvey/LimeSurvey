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

//this line is from old part (see old_templateTopbar.php), it has always been false
$importModal = false;
?>

<?php if ($importModal) : ?>
    <?php $this->renderPartial('themeOptions/import_modal', ['importTemplate' => 'importtemplate', 'importModal' => 'importModal']); ?>
<?php endif; ?>

<!-- theme dropdown select boxes-->

            <!-- Right Menu -->
<div class="mt-3 mb-3">
    <div class="row row-cols-auto align-items-center justify-content-end gx-2">
        <!-- Theme Select Box -->
        <label class="col col-form-label text-nowrap" for='templatedir'><?php eT("Theme:"); ?></label>
        <div class="col">
            <select class="col listboxtemplates form-select activate-search" id='templatedir' name='templatedir'
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
                    'class'    => "col listboxtemplates form-select activate-search",
                    'onchange' => "javascript:  var uri = new Uri('" . $this->createUrl("admin/themes",
                            [
                                'sa'           => 'view',
                                'editfile'     => $relativePathEditfile,
                                'templatename' => $templatename
                            ]) . "'); uri.addQueryParam('screenname',this.value); window.open(uri.toString(), '_top')"
                ]); ?>
        </div>
<!--        @TODO unused button???-->
        <?php if (isset($fullpagebar['savebutton']['form'])) : ?>
            <a class="btn btn-primary" href="#" role="button" id="save-form-button"
               data-form-id="<?php echo $fullpagebar['savebutton']['form']; ?>">
                <span class="ri-check-fill"></span>
                <?php eT("Save"); ?>
            </a>
        <?php endif; ?>

    </div>

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
                'htmlOptions' => ['class' => 'mt-1'],
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
</div>