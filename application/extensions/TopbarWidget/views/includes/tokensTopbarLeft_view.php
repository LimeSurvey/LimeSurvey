<!-- Display tokens -->
<?php if ($hasTokensReadPermission): ?>
    <div class="d-inline-flex">
        <a class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("admin/tokens/sa/browse/surveyid/$oSurvey->sid"); ?>' role="button">
            <span class="ri-list-unordered "></span>
            <?php eT("Display participants"); ?>
        </a>
    </div>
<?php endif; ?>

<!-- Create and Import tokens -->
<?php if ($hasTokensCreatePermission || $hasTokensImportPermission): ?>
    <!-- Create tokens -->
    <?php $createDropdownItems = $this->render('includes/tokensCreateDropdownItems', get_defined_vars(), true); ?>

    <div class="d-inline-flex">
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-create-token-button',
            'id' => 'ls-create-token-button',
            'text' => gT('Create...'),
            'icon' => 'ri-add-circle-fill',
            'isDropDown' => true,
            'dropDownContent' => $createDropdownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>
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
            'icon' => 'icon-emailtemplates',
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
            <i class="ri-group-fill"></i>
            <?php eT("View in CPDB"); ?>
        </a>
    </div>
<?php endif; ?>

<div class="d-inline-flex">
<!-- Survey Quotas -->
    <a class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="fa fa-tasks"></span>
        <?php eT("Survey quotas"); ?>
    </a>
</div>
