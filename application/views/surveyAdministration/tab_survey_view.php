<?php
/**
 * Tabs for survey
 *
 * This file render the tabs used while creating / editing a survey
 * It doesn't render the tab content
 */

App()->getClientScript()->registerScript("tab-survey-view-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN);
$activeTab = Yii::app()->request->getParam('tab', 'create');


switch($activeTab) {
    case 'create':
        $activeForm = 'addnewsurvey';
        // $label = '<i class="ri-check-fill"></i>&nbsp;'.gT("Save");
        $label = '<i class="ri-check-fill"></i>&nbsp;'.gT("Save");

        break;
    case 'copy':
        $activeForm = 'copysurveyform';
        $label = '<i class="ri-file-copy-line"></i>&nbsp;'.gT('Copy');
        break;
}

App()->getClientScript()->registerScript("tab-survey-view-tab-switch-conditions", "
    $('#save-form-button').attr('data-form-id', '".$activeForm."');
    $('#save-form-button').html('".$label."');
    $('#create-import-copy-survey>li>a').on('show.bs.tab', function(e){
        $('#save-form-button').attr('data-form-id', e.target.getAttribute('data-form-id'));
        // NB: button-title is equal to tab title except for 'Create' -> 'Save'
        $('#save-form-button').html($(e.target).data('button-title'));
    });
", LSYii_ClientScript::POS_POSTSCRIPT);
?>


<!-- Tabs -->
<ul class="nav nav-tabs" id="create-import-copy-survey" role="tablist">

    <!-- Create -->
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $activeTab === 'create' ? 'active' : ''?>" role="tab" data-bs-toggle="tab" data-button-title="<i class='ri-check-fill'></i>&nbsp;<?= gT('Save'); ?>" data-form-id="addnewsurvey" href='#general'>
            <?php  eT("Create"); ?>
        </a>
    </li>


    <?php if ($action === "newsurvey"): ?>
        <!-- Copy -->
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $activeTab === 'copy' ? 'active' : ''?>" role="tab" data-bs-toggle="tab" data-button-title="<i class='ri-file-copy-line'></i>&nbsp;<?= gT('Copy'); ?>" data-form-id="copysurveyform" href="#copy">
                <?php  eT("Copy"); ?>
            </a>
        </li>

    <?php elseif($action === "editsurveysettings"): ?>

        <!-- Panel integration -->
        <li class="nav-item" role="presentation">
            <a role="tab" data-bs-toggle="tab" href="#panelintegration">
                <?php  eT("Panel integration"); ?>
            </a>
        </li>

        <!-- Resources -->
        <li class="nav-item" role="presentation">
            <a role="tab" data-bs-toggle="tab" href="#resources">
                <?php  eT("Resources"); ?>
            </a>
        </li>

        <!-- Plugins -->
        <?php if(isset($pluginSettings)): ?>
            <li class="nav-item" role="presentation">
                <a role="tab" data-bs-toggle="tab" href="#pluginsettings">
                    <?php  eT("Plugins"); ?>
                </a>
            </li>
        <?php endif;?>
    <?php endif; ?>
</ul>
