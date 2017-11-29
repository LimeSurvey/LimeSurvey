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
?>

<!-- Tabs -->
<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">

    <!-- Create -->
    <li role="presentation" <?php if($active=='create'){echo 'class="active"';}?>>
        <a role="tab" data-toggle="tab" href='#general'>
            <?php  eT("Create"); ?>
        </a>
    </li>


    <?php if ($action == "newsurvey"): ?>
        <!-- Import -->
        <li role="presentation" <?php if($active=='import'){echo 'class="active"';}?>>
            <a role="tab" data-toggle="tab" href="#import">
                <?php  eT("Import"); ?>
            </a>
        </li>

        <!-- Copy -->
        <li role="presentation" <?php if($active=='copy'){echo 'class="active"';}?>>
            <a role="tab" data-toggle="tab" href="#copy">
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
