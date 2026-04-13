<?php

/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
if (isset($data)) {
    extract($data);
}
$count = 0;
if (isset($scripts)) {
    echo $scripts;
}


$iSurveyID                                = Yii::app()->request->getParam('surveyid');
Yii::app()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";
initKcfinder();

PrepareEditorScript(false, $this);
?>
<!-- security notice -->
<div class="mb-3">
            <label class="form-label" for='showsurveypolicynotice'><?php  eT("Show privacy policy text with mandatory checkbox:") ; ?></label>
    <div class="">
                <div class="btn-group" data-bs-toggle="buttons">
                    <input class="btn-check" type="radio" id="showsurveypolicynotice_0" name="showsurveypolicynotice" value="0" <?=$oSurvey->showsurveypolicynotice==0 ? 'checked' : ''?> autocomplete="off">
                    <label for="showsurveypolicynotice_0" class="btn btn-outline-secondary">
                        <?=gT("Don't show");?>
            </label>
                    <input class="btn-check" type="radio" id="showsurveypolicynotice_1" name="showsurveypolicynotice" value="1" <?=$oSurvey->showsurveypolicynotice==1 ? 'checked' : ''?> autocomplete="off">
                    <label for="showsurveypolicynotice_1" class="btn btn-outline-secondary">
                        <?=gT("Inline text");?>
            </label>
                    <input class="btn-check" type="radio" id="showsurveypolicynotice_2" name="showsurveypolicynotice" value="2" <?=$oSurvey->showsurveypolicynotice==2 ? 'checked' : ''?> autocomplete="off">
                    <label for="showsurveypolicynotice_2" class="btn btn-outline-secondary">
                        <?=gT("Collapsible text");?>
            </label>
        </div>
    </div>
</div>
<nav>
    <div class="nav nav-tabs" id="edit-survey-datasecurity-element-language-selection" role="tablist">
        <?php foreach ($aTabTitles as $i => $eachtitle): ?>
            <button class="nav-link <?php if ($count == 0) {
                echo "active";
            } ?>"  role="tab" data-bs-toggle="tab" data-bs-target="#editdatasecele-<?php echo $count;
            $count++; ?>" type="button">
                <?php echo $eachtitle; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="tab-content">
        <?php foreach ($aTabContents as $i => $sTabContent): ?>
            <?php
            echo $sTabContent;
            ?>
        <?php endforeach; ?>
    </div>
</nav>

<?php App()->getClientScript()->registerScript("EditSurveyDataSecurityTabs",
    "
$('#edit-survey-text-element-language-selection').find('a').on('shown.bs.tab', function(e){
    try{ $(e.relatedTarget).find('textarea').ckeditor(); } catch(e){ }
})",
    LSYii_ClientScript::POS_POSTSCRIPT
); ?>
