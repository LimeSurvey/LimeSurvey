<?php

/**
 * @var SurveyAdministrationController $this
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */

if (isset($data)) {
    extract($data);
}

if (isset($scripts)) {
    echo $scripts;

    $iSurveyID = App()->request->getParam('surveyid');
    App()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";
    initKcfinder();
}

$cs = Yii::app()->getClientScript();
$cs->registerPackage('select2-bootstrap');

$adminlang = Yii::app()->session['adminlang'];
$aTabContents = $this->aData['aTabContents'];
$aTabTitles = $this->aData['aTabTitles'];
$count = 0;

PrepareEditorScript(false, $this);
?>
<!-- Text Elements Tabs -->
<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
    <?php foreach ($aTabTitles as $i => $title): ?>
        <li role="presentation" class="nav-item">
            <a class="nav-link <?= ($count === 0 || (strpos((string) $title, ' (' . gT("Base language") . ')'))) ? "active" : "" ?>"
               data-bs-toggle="tab"
               href="#edittxtele-<?php echo $count; $count++; ?>">
                <?php echo $title; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="tab-content">
    <?php foreach ($aTabContents as $i => $content): ?>
        <?php echo $content; ?>
    <?php endforeach; ?>
</div>

<?php App()->getClientScript()->registerScript("EditSurveyTextTabs",
    "
$('#edit-survey-text-element-language-selection').find('a').on('shown.bs.tab', function(e){
    try{ $(e.relatedTarget).find('textarea').ckeditor(); } catch(e){ }
})",
    LSYii_ClientScript::POS_POSTSCRIPT); ?>
