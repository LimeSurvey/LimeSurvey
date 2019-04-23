<?php
/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
if(isset($data)){
    extract($data);
}
 $count=0;
 if(isset($scripts))
    echo $scripts;


    $iSurveyID = Yii::app()->request->getParam('surveyid');
    Yii::app()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";
    initKcfinder();

PrepareEditorScript(false, $this);
?>
<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
    <?php foreach ($aTabTitles as $i=>$eachtitle):?>
        <li role="presentation" class="<?php if($count==0) {echo "active"; }?>">
            <a data-toggle="tab" href="#edittxtele-<?php echo $count; $count++; ?>">
                <?php echo $eachtitle;?>
            </a>
        </li>
    <?php endforeach;?>
</ul>

<br/>

<div class="tab-content">
<?php foreach ($aTabContents as $i=>$sTabContent):?>
    <?php
        echo $sTabContent;
    ?>
<?php endforeach; ?>
</div>

<?php App()->getClientScript()->registerScript("EditSurveyTextTabs", "
$('#edit-survey-text-element-language-selection').find('a').on('shown.bs.tab', function(e){
    try{ $(e.relatedTarget).find('textarea').ckeditor(); } catch(e){ }
})", LSYii_ClientScript::POS_POSTSCRIPT); ?>
