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

<?php if ($importModal) : ?>
    <?php $this->renderPartial('themeOptions/import_modal', ['importTemplate' => 'importtemplate', 'importModal' => 'importModal']); ?>
<?php endif; ?>

<!-- Template Editor -->
<div class="col-12 templateeditor">

    <?php if (!is_template_editable($templatename)) : ?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <span class="fa fa-info-sign"></span>&nbsp;&nbsp;&nbsp;
            <strong>
                <?php eT('Note: This is a standard theme.'); ?>
            </strong>
            <?php
            printf(gT('If you want to modify it %s you can extend it%s.'),
                "<a href='#' title=\"" . gT("Extend theme") . "\""
                . " onclick=\"javascript: copyprompt('" . gT("Please enter the name for the new theme:") . "', '" . gT("extends_") . "$templatename', '$templatename', 'copy')\">",
                '</a>');
            ?>
        </div>
    <?php endif; ?>
    <?php if ((int)$templateapiversion < (int)App()->getConfig("templateapiversion")) : ?>
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="h4">
                <span class="fa fa-info-sign"></span>
                <?php eT('This theme is out of date.'); ?>
            </div>
            <?php
            printf(gT("We can not guarantee optimum operation. It would be preferable to no longer use it or to make it compatible with the version %s of the LimeSurvey API."),
                intval(App()->getConfig("versionnumber")));
            ?>
        </div>
    <?php endif; ?>
</div>
