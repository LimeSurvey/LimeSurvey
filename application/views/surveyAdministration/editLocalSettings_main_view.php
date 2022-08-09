<?php
/**
 * General container for edit survey action
 *
 * @var SurveyAdministrationController $this
 * @var Survey $oSurvey
 */

$templateData['oSurvey']         = $oSurvey;
$templateData['oSurveyOptions']  = $oSurvey->oOptionLabels;
$templateData['bShowInherited']  = $oSurvey->showInherited;
$templateData['bShowAllOptions'] = true;
$templateData['optionsOnOff']    = $optionsOnOff;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyGeneralSettings');

Yii::app()->getClientScript()->registerScript("editLocalSettings_formid_" . $entryData['name'],
    "
var formId = '" . $entryData['name'] . "';
",
    LSYii_ClientScript::POS_BEGIN
);

$count = 0;
if (isset($scripts)) {
    echo $scripts;
}
?>
<!-- START editLocalSettings -->
<div class="row">
    <div class="col-12">
        <h3 class="pagetitle"><?php eT($entryData['title']); ?></h3>
        <!-- Edition container -->
        <!-- Form -->
        <?php
        if (empty($templateData['noform']) || $templateData['noform'] !== true) {
            echo CHtml::form(["admin/database/index/" . $entryData['action']], 'post', ['id' => $entryData['name'], 'name' => $entryData['name'], 'class' => ' form30']);
        }
        ?>
        <div class="row">
            <div class="<?= $entryData['classes'] ?>">
                <?php $this->renderPartial($entryData['partial'], $templateData); ?>
            </div>
        </div>

        <?php
        if (empty($templateData['noform']) || $templateData['noform'] !== true) { ?>
            <!--
      This hidden button is now necessary to save the form.
      Before, there where several nested forms in Global settings, which is invalid in html
    The submit button from the "import resources" was submitting the whole form.
    Now, the "import resources" is outside the global form, in a modal ( subview/import_ressources_modal.php)
      So the globalsetting form needs its own submit button
      -->
            <input type="hidden" name="action" value="<?= $entryData['action'] ?>"/>
            <input type="hidden" name="sid" value="<?php echo $surveyid; ?>"/>
            <input type="hidden" name="surveyid" value="<?php echo $surveyid; ?>"/>
            <input type="hidden" name="responsejson" value="1"/>
            <input type='submit' class="d-none" id="globalsetting_submit"/>
            <?php echo CHtml::endForm() ?>
        <?php } ?>
    </div>
</div>
<!-- END editLocalSettings -->
<?php

Yii::app()->getClientScript()->registerScript("editLocalSettings_submit_" . $entryData['name'],
    "
$('#" . $entryData['name'] . "').off('.editLocalsettings');

$('#" . $entryData['name'] . "').on('submit.editLocalsettings', function(e){
    e.preventDefault();
    var data = $(this).serializeArray();
    var uri = $(this).attr('action');
    $.ajax({
        url: uri,
        method:'POST',
        data: data,
        success: function(result){
            console.log({result: result});
            if(result.redirecturl != undefined ){
                window.location.href=result.redirecturl;
            } else {
                window.location.reload();
            }
        },
        error: function(result){
            console.log({result: result});
        }
    });
    return false;
});

$('.text-option-inherit').on('change', function(e){
    var newValue = $(this).find('.btn-check:checked').val();
    var parent = $(this).parent().parent();
    var inheritValue = parent.find('.inherit-edit').data('inherit-value');
    var savedValue = parent.find('.inherit-edit').data('saved-value');

    if (newValue == 'Y'){
        parent.find('.inherit-edit').addClass('d-none').removeClass('show').val(inheritValue);
        parent.find('.inherit-readonly').addClass('show').removeClass('d-none');
    } else {
        var inputValue = (savedValue === inheritValue) ? \"\" : savedValue;
        parent.find('.inherit-edit').addClass('show').removeClass('d-none').val(inputValue);
        parent.find('.inherit-readonly').addClass('d-none').removeClass('show');
    }
});
",
    LSYii_ClientScript::POS_POSTSCRIPT
);

?>
