<!-- Display tokens -->
<?php if ($hasTokensReadPermission): ?>
    <a class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("admin/tokens/sa/browse/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="ri-list-unordered text-success"></span>
        <?php eT("Display participants"); ?>
    </a>
<?php endif; ?>

<!-- Create and Import tokens -->
<?php if ($hasTokensCreatePermission || $hasTokensImportPermission): ?>
    <!-- Create tokens -->
    <div class="btn-group">
    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="ri-add-circle-fill text-success"></span>
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
    <a class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="icon-token_manage text-success"></span>
        <?php eT("Manage attributes"); ?>
    </a>
<?php endif; ?>

<!-- Export tokens to CSV file -->
<?php if ($hasTokensExportPermission): ?>
    <a class="btn btn-outline-secondary pjax" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/exportdialog/surveyid/$oSurvey->sid"); ?>" role="button">
        <span class="icon-exportcsv"></span>
        <?php eT("Export"); ?>
    </a>
<?php endif; ?>

<!-- EMAILS -->
<?php if ($hasTokensUpdatePermission):?>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="ri-mail-settings-line text-success"></span>
            <?php eT("Invitations & reminders");?> <span class="caret"></span>
        </button>

        <ul class="dropdown-menu">
            <?php if ($hasTokensUpdatePermission): ?>

            <!-- Send email invitation -->
            <li>
                <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/email/surveyid/$oSurvey->sid"); ?>" >
                    <span class="ri-mail-send-fill"></span>
                    <?php eT("Send email invitation"); ?>
                </a>
            </li>

            <!-- Send email reminder -->
            <li>
                <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/email/action/remind/surveyid/$oSurvey->sid"); ?>" >
                    <span class="ri-mail-volume-fill"></span>
                    <?php eT("Send email reminder"); ?>
                </a>
            </li>

            <!-- Edit email template -->
            <!-- Send email invitation -->
            <li>
                <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/emailtemplates/sa/index/surveyid/$oSurvey->sid"); ?>" >
                    <span class="ri-mail-line"></span>
                    <?php eT("Edit email templates"); ?>
                </a>
            </li>
            <?php endif; ?>

            <li role="separator" class="dropdown-divider"></li>

            <!-- Bounce processing -->
            <?php if ($hasTokensUpdatePermission):?>
                <?php if($oSurvey->bounceprocessing != 'N' ||  ($oSurvey->bounceprocessing == 'G' && App()->getConfig('bounceaccounttype') != 'off')):?>
                    <?php if (function_exists('imap_open')):?>
                        <li>
                            <a class="dropdown-item" href="#" id="startbounceprocessing" data-url="<?php echo Yii::App()->createUrl("admin/tokens/sa/bounceprocessing/surveyid/$oSurvey->sid"); ?>" >
                                <span class="ui-bounceprocessing"></span>
                                <?php eT("Start bounce processing"); ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <?php $eMessage = gT("The imap PHP library is not installed or not activated. Please contact your system administrator."); ?>
                    <?php endif;?>
                <?php else: ?>
                    <?php $eMessage = gT("Bounce processing is deactivated either application-wide or for this survey in particular."); ?>
                <?php endif;?>
            <?php else:?>
                <?php $eMessage = gT("We are sorry but you don't have permissions to do this."); ?>
            <?php endif;?>

            <?php if (isset($eMessage)):?>
                <li class="disabled">
                    <a  class="dropdown-item" href="#" class="disabled" data-bs-toggle="tooltip" data-bs-placement="bottom" title='<?php echo $eMessage; ?>'>
                        <span class="ui-bounceprocessing"></span>
                        <?php eT("Start bounce processing"); ?>
                    </a>
                </li>
            <?php endif;?>

            <!-- Bounce settings -->
            <li>
                <a class="dropdown-item" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/bouncesettings/surveyid/$oSurvey->sid"); ?>" >
                    <span class="ri-settings-5-fill"></span>
                    <?php eT("Bounce settings"); ?>
                </a>
            </li>
        </ul>
    </div>

    <!-- Generate tokens -->
    <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/tokenify/surveyid/$oSurvey->sid"); ?>" role="button">
        <span class="ri-settings-5-fill text-success"></span>
        <?php eT("Generate tokens"); ?>
    </a>

    <!-- View participants of this survey in CPDB -->
    <a class="btn btn-outline-secondary" href="#" role="button" onclick="window.LS.sendPost('<?php echo Yii::App()->createUrl("/admin/participants/sa/displayParticipants"); ?>',false,{'searchcondition': 'surveyid||equal|| <?php echo $oSurvey->sid ?>'});">
        <i class="ri-group-fill text-success"></i>
        <?php eT("View in CPDB"); ?>
    </a>
<?php endif; ?>

<!-- Survey Quotas -->
<a class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$oSurvey->sid"); ?>' role="button">
    <span class="ri-bar-chart-horizontal-fill"></span>
    <?php eT("Survey quotas"); ?>
</a>
