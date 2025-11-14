<?php
/**
 * General options
 * @var AdminController $this
 * @var Survey $oSurvey
 */

$count = 0;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyEmailTemplates');

App()->getClientScript()->registerScript("EmailTemplateViews_variables",
    "
var sReplaceTextConfirmation='" . gT("This will replace the existing text. Continue?", "js") . "';
var sKCFinderLanguage='" . sTranslateLangCode2CK(App()->language) . "';

var LS = LS || {};  // namespace
    LS.lang = LS.lang || {};  // object holding translations
    LS.lang['Remove attachment'] = '" . gT("Remove attachment") . "';
    LS.lang['Edit condition'] = '" . gT("Edit condition") . "';
",
    LSYii_ClientScript::POS_BEGIN);

?>
<div class="side-body">
    <h3 aria-level="1"  ><?php eT("Edit email templates"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(['admin/emailtemplates/sa/update/surveyid/' . $surveyid], 'post', ['name' => 'emailtemplates', 'class' => '', 'id' => 'emailtemplates']); ?>
            <ul class="nav nav-tabs">
                <?php foreach ($oSurvey->allLanguages as $grouplang): ?>
                    <li role="presentation" class="nav-item">
                        <a class="nav-link <?= ($count == 0) ? 'active' : '' ?>" data-bs-toggle="tab" href='#tab-<?= $grouplang ?>'>
                            <?php $count++ ?>
                            <?= getLanguageNameFromCode($grouplang, false) . " " . (($grouplang == $oSurvey->language) ? "(" . gT("Base language") . ")" : "") ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
                <?php
                $count = 0;
                $active = 'show active';
                foreach ($oSurvey->allLanguages as $key => $grouplang) {
                    $bplang = $bplangs[$key];
                    $esrow = $attrib[$key];
                    $aDefaultTexts = $defaulttexts[$key];

                    $this->renderPartial('/admin/emailtemplates/email_language_tab', compact('ishtml', 'surveyid', 'grouplang', 'bplang', 'esrow', 'aDefaultTexts', 'active'));

                    if ($count == 0) {
                        $count++;
                        $active = '';
                    }
                }
                ?>
                <p>
                    <?php echo CHtml::htmlButton(gT('Save'), ['type' => 'submit', 'value' => 'save', 'name' => 'save', 'class' => 'd-none']) ?>
                    <?php echo CHtml::htmlButton(gT('Save and close'), ['type' => 'submit', 'value' => 'saveclose', 'name' => 'save', 'class' => 'd-none']) ?>
                    <?php echo CHtml::hiddenField('action', 'tokens'); ?>
                    <?php echo CHtml::hiddenField('language', $esrow->surveyls_language); ?>
                </p>
            </div>
            <?php echo CHtml::endForm() ?>

        </div>
    </div>
</div>

<div class="modal modal-large fade" tabindex="-1" role="dialog" id="kc-modal-open">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" aria-level="2"><?= gT("Choose file to add") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="browseiframe" frameBorder="0" style="min-height: 600px; height:100%; width: 100%;" src="about:blank"></iframe>
            </div>
            <div class='modal-footer'>
                <button type="button" class='btn btn-cancel' data-bs-dismiss='modal'><?php eT("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="attachment-relevance-editor" class="modal fade">
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class="modal-title"><?php eT("Condition"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class='modal-body'>
                <div class='input-group'>
                    <div class="input-group-text">{</div>
                    <textarea class='form-control' id='attachment-relevance-condition'></textarea>
                    <div class="input-group-text">}</div>
                </div>
            </div>
            <div class='modal-footer'>
                <button type="button" class='btn btn-outline-secondary' data-bs-dismiss='modal'>
                    <?php eT("Close"); ?>
                </button>
                <button type="button" class='btn btn-primary'>
                    <?php eT("Add"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
