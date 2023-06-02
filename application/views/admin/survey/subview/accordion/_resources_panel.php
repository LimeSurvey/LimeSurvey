<?php
/**
 * Resources panel tab
 **/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResources');

App()->getClientScript()->registerScript(
    "resources-panel-variables",
    "
var jsonUrl = '';
var sAction = '';
var sParameter = '';
var sTargetQuestion = '';
var sNoParametersDefined = '';
var sAdminEmailAddressNeeded = '" . gT("If you are using surveys with a closed participant group or notifications emails you need to set an administrator email address.", 'js') . "'
var sURLParameters = '';
var sAddParam = '';
",
    LSYii_ClientScript::POS_BEGIN
);

//The resources panel is a little special thus the unorganized html
// @TODO Fix rendering!
?>

<!-- resources panel -->
<div id='resources'>
    <div class="row">
        <!-- Export -->
        <div class="col-md-3">
            <?php
            echo TbHtml::dropDownList(
                'fileTypeShow',
                'fileTypeShow',
                array(
                    'files' => gT('Files', 'unescaped'),
                    'flash' => gT('Flash', 'unescaped'),
                    'images' => gT('Images', 'unescaped')
                ),
                array(
                    'class'     => 'form-select',
                    'data-href' => App()->request->getBaseUrl() . "/vendor/kcfinder/browse.php?language=" . sTranslateLangCode2CK(App()->language)
                )
            );
            ?>
        </div>
        <div class="col-md-auto mb-3">
            <a href="<?php echo $this->createUrl('admin/export/sa/resources/export/survey/surveyid/'.$surveyid); ?>" target="_blank" class="btn btn-outline-secondary">
                <span class="ri-upload-fill"></span>
                <?php eT("Export resources as ZIP archive") ?>
            </a>
            <a class="btn btn-outline-secondary" href="" target='_blank' data-bs-toggle="modal" data-bs-target="#importRessourcesModal">
                <span class="ri-download-fill"></span>
                <?php eT("Import resources ZIP archive"); ?>
            </a>
        </div>
        <div class="col-12 file-manager">
            <iframe
                id="browseiframe"
                src="<?php echo App()->request->getBaseUrl(); ?>/vendor/kcfinder/browse.php?language='<?php echo sTranslateLangCode2CK(App()->language); ?>'"
                width="100%"
                height="600px">
            </iframe>
        </div>
    </div>
</div>
<div>
    <?php $this->renderPartial('/admin/survey/subview/import_ressources_modal', ['surveyid' => $surveyid, 'ZIPimportAction' => $ZIPimportAction]); ?>
</div>

<?php
App()->getClientScript()->registerScript(
    "RessourcesPanelScripts",
    "
    $('#fileTypeShow').on('change', function(e){ e.preventDefault(); $('#browseiframe').attr('src', $(this).data('href')+'&type='+$(this).val()) });
    ",
    LSYii_ClientScript::POS_POSTSCRIPT
);
?>