<?php
/**
 * Tabs for survey
 *
 * This file render the tabs used while creating / editing a survey
 * It doesn't render the tab content
 */

$active = (isset($_GET['tab']))?$_GET['tab']:'create';

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
        $label = '<i class="fa fa-save"></i>&nbsp;'.gT("Save");
        break;
    case 'import':
        $activeForm = 'importsurvey'; 
        $label = '<i class="fa fa-download"></i>&nbsp;'.gT('Import');
        break;
    case 'copy':
        $activeForm = 'copysurveyform'; 
        $label = '<i class="fa fa-copy"></i>&nbsp;'.gT('Copy');
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
<ul class="nav nav-tabs" id="create-import-copy-survey">

    <!-- Create -->
    <li role="presentation" <?php if($active=='create'){echo 'class="active"';}?>>
        <a role="tab" data-toggle="tab" data-button-title="<i class='fa fa-save'></i>&nbsp;<?= gT('Save'); ?>" data-form-id="addnewsurvey" href='#general'>
            <i class="fa fa-plus-circle"></i>&nbsp;
            <?php  eT("Create"); ?>
        </a>
    </li>


    <?php if ($action == "newsurvey"): ?>
        <!-- Import -->
        <li role="presentation" <?php if($active=='import'){echo 'class="active"';}?>>
            <a role="tab" data-toggle="tab" data-button-title=" <span class='icon-import '></span>&nbsp;<?= gT('Import'); ?>" data-form-id="importsurvey" href="#import">
            <span class="icon-import text-success"></span>&nbsp;
                <?php  eT("Import"); ?>
            </a>
        </li>

        <!-- Copy -->
        <li role="presentation" <?php if($active=='copy'){echo 'class="active"';}?>>
            <a role="tab" data-toggle="tab" data-button-title="<i class='fa fa-copy'></i>&nbsp;<?= gT('Copy'); ?>" data-form-id="copysurveyform" href="#copy">
                <i class="fa fa-copy"></i>&nbsp;
                <?php  eT("Copy"); ?>
            </a>
        </li>

    <?php elseif($action == "editsurveysettings"): ?>

        <!-- Panel integration -->
        <li role="presentation">
            <a role="tab" data-toggle="tab" href="#panelintegration">
                <?php  eT("Panel integration"); ?>
            </a>
        </li>

        <!-- Resources -->
        <li role="presentation">
            <a role="tab" data-toggle="tab" href="#resources">
                <?php  eT("Resources"); ?>
            </a>
        </li>

        <!-- Plugins -->
        <?php if(isset($pluginSettings)): ?>
            <li role="presentation">
                <a role="tab" data-toggle="tab" href="#pluginsettings">
                    <?php  eT("Plugins"); ?>
                </a>
            </li>
        <?php endif;?>
    <?php endif; ?>
</ul>
