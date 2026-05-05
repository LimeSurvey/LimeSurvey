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
<div class="row mb-3">
    <div class="col-lg-6">
        <label class="form-label" id="showsurveypolicynotice-label"><?php  eT("Show privacy policy text with mandatory checkbox:") ; ?></label>
        <div>
            <div class="btn-group" data-bs-toggle="buttons" role="radiogroup" aria-labelledby="showsurveypolicynotice-label" >
                <input class="btn-check" type="radio" id="showsurveypolicynotice_0" name="showsurveypolicynotice" value="0" <?=$oSurvey->showsurveypolicynotice == 0 ? 'checked' : ''?> autocomplete="off">
                <label for="showsurveypolicynotice_0" class="btn btn-outline-secondary">
                    <?=gT("Don't show");?>
                </label>
                <input class="btn-check" type="radio" id="showsurveypolicynotice_1" name="showsurveypolicynotice" value="1" <?=$oSurvey->showsurveypolicynotice == 1 ? 'checked' : ''?> autocomplete="off">
                <label for="showsurveypolicynotice_1" class="btn btn-outline-secondary">
                    <?=gT("Inline text");?>
                </label>
                <input class="btn-check" type="radio" id="showsurveypolicynotice_2" name="showsurveypolicynotice" value="2" <?=$oSurvey->showsurveypolicynotice == 2 ? 'checked' : ''?> autocomplete="off">
                <label for="showsurveypolicynotice_2" class="btn btn-outline-secondary">
                    <?=gT("Collapsible text");?>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="form-label" id="showtokenpolicy-label"><?php  eT("Show privacy policy on token form:") ; ?></label>
        <div>
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'showtokenpolicy',
                'checkedOption' => $oSurvey->showtokenpolicy,
                'htmlOptions' => [
                    'aria-labelledby' => "showtokenpolicy-label",
                    'aria-describedby' => "showtokenpolicy-help"
                ],
                'selectOptions' => ($bShowInherited)
                    ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->showtokenpolicy . " ᴵ" ])
                    : $optionsOnOff
            ]) ?>
            <div id="showtokenpolicy-help" class="form-text"><?php eT("Only applies when 'Show privacy policy text with mandatory checkbox' is set to Inline text or Collapsible text.", 'unescaped'); ?></div>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="form-label" id="showregisterpolicy-label"><?php  eT("Show privacy policy on register form:") ; ?></label>
        <div>
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'showregisterpolicy',
                'checkedOption' => $oSurvey->showregisterpolicy,
                'htmlOptions' => [
                    'aria-labelledby' => "showregisterpolicy-label",
                    'aria-describedby' => "showregisterpolicy-help"
                ],
                'selectOptions' => ($bShowInherited)
                    ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->showregisterpolicy . " ᴵ" ])
                    : $optionsOnOff
            ]) ?>
            <div id="showregisterpolicy-help" class="form-text"><?php eT("Only applies when 'Show privacy policy text with mandatory checkbox' is set to Inline text or Collapsible text.", 'unescaped'); ?></div>
        </div>
    </div>
</div>

<nav>
    <div class="nav nav-tabs" id="edit-survey-datasecurity-element-language-selection" role="tablist">
        <?php foreach ($aTabTitles as $i => $eachtitle): ?>
            <button class="nav-link <?= $count == 0 ? "active" : '' ?>" role="tab" data-bs-toggle="tab" data-bs-target="#editdatasecele-<?= $count ?>" type="button">
                <?= $eachtitle; ?>
            </button>
            <?php $count++ ?>
        <?php endforeach; ?>
    </div>
    <div class="tab-content">
        <?php foreach ($aTabContents as $i => $sTabContent): ?>
            <?= $sTabContent ?>
        <?php endforeach; ?>
    </div>
</nav>
