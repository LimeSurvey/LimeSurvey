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
<div class="container-fluid">
    <div class="row">
        <!-- security notice -->
        <div class="form-group">
            <label class="control-label" for='showsurveypolicynotice'><?php  eT("Show survey policy text with mandatory checkbox:") ; ?></label>
            <div class="">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?=$oSurvey->showsurveypolicynotice==0 ? 'active' : ''?>" >
                        <input type="radio" name="showsurveypolicynotice" value="0" <?=$oSurvey->showsurveypolicynotice==0 ? 'checked' : ''?> autocomplete="off"> <?=gT("Don't show");?>
                    </label>
                    <label class="btn btn-default <?=$oSurvey->showsurveypolicynotice==1 ? 'active' : ''?>" >
                        <input type="radio" name="showsurveypolicynotice" value="1" <?=$oSurvey->showsurveypolicynotice==1 ? 'checked' : ''?> autocomplete="off"> <?=gT("Inline text");?>
                    </label>
                    <label class="btn btn-default <?=$oSurvey->showsurveypolicynotice==2 ? 'active' : ''?>" >
                        <input type="radio" name="showsurveypolicynotice" value="2" <?=$oSurvey->showsurveypolicynotice==2 ? 'checked' : ''?> autocomplete="off"> <?=gT("Collapsible text");?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row ls-space margin top-15">
        <ul class="nav nav-tabs" id="edit-survey-datasecurity-element-language-selection">
            <?php foreach ($aTabTitles as $i=>$eachtitle):?>
                <li role="presentation" class="<?php if($count==0) {echo "active"; }?>">
                    <a data-toggle="tab" href="#editdatasecele-<?php echo $count; $count++; ?>">
                        <?php echo $eachtitle;?>
                    </a>
                </li>
            <?php endforeach;?>
        </ul>
        <div class="tab-content">
            <?php foreach ($aTabContents as $i=>$sTabContent):?>
                <?php
                    echo $sTabContent;
                ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php App()->getClientScript()->registerScript("EditSurveyDataSecurityTabs", "
$('#edit-survey-text-element-language-selection').find('a').on('shown.bs.tab', function(e){
    try{ $(e.relatedTarget).find('textarea').ckeditor(); } catch(e){ }
})", LSYii_ClientScript::POS_POSTSCRIPT); ?>
