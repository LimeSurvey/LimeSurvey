<!-- Display tokens -->
<?php if ($hasTokensReadPermission): ?>
    <a class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("admin/tokens/sa/browse/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="ri-list-unordered"></span>
        <?php eT("Display participants"); ?>
    </a>
<?php endif; ?>

<!-- Create and Import tokens -->
<?php if ($hasTokensCreatePermission || $hasTokensImportPermission): ?>
    <!-- Create tokens -->
    <div class="btn-group">
    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="ri-add-circle-fill"></span>
        <?php eT("Create...");?> <span class="caret"></span>
    </button>

    <!-- Add new token entry -->
    <ul class="dropdown-menu">
    <?php if ($hasTokensCreatePermission): ?>
    <li>
        <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/addnew/surveyid/$oSurvey->sid"); ?>" >
            <i class="ri-add-circle-fill"></i>
            <?php eT("Add participant"); ?>
        </a>
    </li>

    <!-- Create dummy tokens -->
    <li>
        <a class="pjax dropdown-item"  href="<?php echo Yii::App()->createUrl("admin/tokens/sa/adddummies/surveyid/$oSurvey->sid"); ?>" >
            <span class="ri-add-box-fill"></span>
            <?php eT("Create dummy participants"); ?>
        </a>
    </li>
    <?php endif; ?>
    <?php if ($hasTokensCreatePermission && $hasTokensImportPermission): ?>
        <li role="separator" class="dropdown-divider"></li>
    <?php endif; ?>
    <!-- Import tokens -->
    <?php if ($hasTokensImportPermission): ?>
        
        <li>
            <h6 class="dropdown-header"><?php eT("Import participants from:"); ?></h6>
        </li>

        <!-- from CSV file -->
        <li>
            <a class="pjax dropdown-item"  href="<?php echo Yii::App()->createUrl("admin/tokens/sa/import/surveyid/$oSurvey->sid") ?>" >
                <span class="ri-upload-fill"></span>
                <?php eT("CSV file"); ?>
            </a>
        </li>

        <!-- from LDAP query -->
        <li>
            <a class="pjax dropdown-item"  href="<?php echo Yii::App()->createUrl("admin/tokens/sa/importldap/surveyid/$oSurvey->sid") ?>" >
                <span class="icon-importldap"></span>
                <?php eT("LDAP query"); ?>
            </a>
        </li>
    <?php endif; ?>
    </ul>
    </div>
<?php endif; ?>
<!-- Manage additional attribute fields -->
<?php if ($hasTokensUpdatePermission || $hasSurveySettingsUpdatePermission): ?>
    <div class="d-inline-flex">
        <a class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$oSurvey->sid"); ?>' role="button">
            <span class="icon-token_manage"></span>
            <?php eT("Manage attributes"); ?>
        </a>
    </div>
<?php endif; ?>

<!-- Export tokens to CSV file -->
<?php if ($hasTokensExportPermission): ?>
    <div class="d-inline-flex">
        <a class="btn btn-outline-secondary pjax" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/exportdialog/surveyid/$oSurvey->sid"); ?>" role="button">
            <span class="icon-exportcsv"></span>
            <?php eT("Export"); ?>
        </a>
    </div>
<?php endif; ?>

<!-- EMAILS -->
<?php if ($hasTokensUpdatePermission):?>
<?php $invRemDropDownItems = $this->render('includes/tokensInvRemDropdownItems', get_defined_vars(), true); ?>
    <div class="d-inline-flex">
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-inv-rem-button',
            'id' => 'ls-inv-rem-button',
            'text' => gT('Invitations & reminders'),
            'remix' => 'ri-mail-settings-line',
            'isDropDown' => true,
            'dropDownContent' => $invRemDropDownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>
    </div>

    <div class="d-inline-flex">
        <!-- Generate tokens -->
        <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/tokenify/surveyid/$oSurvey->sid"); ?>" role="button">
            <span class="icon-do"></span>
            <?php eT("Generate tokens"); ?>
        </a>
    </div>
    <div class="d-inline-flex">
        <!-- View participants of this survey in CPDB -->
        <a class="btn btn-outline-secondary" href="#" role="button" onclick="window.LS.sendPost('<?php echo Yii::App()->createUrl("/admin/participants/sa/displayParticipants"); ?>',false,{'searchcondition': 'surveyid||equal|| <?php echo $oSurvey->sid ?>'});">
            <i class="fa fa-users"></i>
            <?php eT("View in CPDB"); ?>
        </a>
    </div>
<?php endif; ?>

<div class="d-inline-flex">
<!-- Survey Quotas -->
    <a class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="ri-bar-chart-horizontal-fill"></span>
        <?php eT("Survey quotas"); ?>
    </a>
</div>
