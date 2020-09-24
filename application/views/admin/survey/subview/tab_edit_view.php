<?php

/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */

 print_r($data['aTabTitles']);
 die();
if (isset($data)) {
    extract($data);
}

if (isset($scripts)) {
    echo $scripts;

    $iSurveyID = App()->request->getParam('surveyid');
    App()->session['FileManagerContent'] = "edit:survey:{$iSurveyID}";
    initKcfinder();
}

$cs = Yii::app()->getClientScript();
$cs->registerPackage('bootstrap-select2');

$adminlang = Yii::app()->session['adminlang'];

?>
<!-- Text Elements Tabs -->
<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
    <?php foreach ($aTabTitles as $i => $title):?>
        <li role="presentation" class="<?php if ($count==0) {echo "active"; }?>">
            <a data-toggle="tab" href="#edittxtele-<?php echo $count; $count++; ?>">
                <?php echo $title;?>
            </a>
        </li>
    <?php endforeach;?>
</ul>

<br/>

<div class="tab-content">
<?php foreach ($aTabContents as $i=>$content):?>
    <?php
        echo $content;
    ?>
<?php endforeach; ?>
</div>

<?php App()->getClientScript()->registerScript("EditSurveyTextTabs", "
$('#edit-survey-text-element-language-selection').find('a').on('shown.bs.tab', function(e){
    try{ $(e.relatedTarget).find('textarea').ckeditor(); } catch(e){ }
})", LSYii_ClientScript::POS_POSTSCRIPT); ?>
