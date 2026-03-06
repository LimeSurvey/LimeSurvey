<!-- Invite & remind menu -->
<ul class="dropdown-menu" role="menu" aria-labelledby="ls-inv-rem-button">
    <?php if ($hasTokensUpdatePermission): ?>

        <!-- Send email invitation -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/email/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-mail-send-fill" aria-hidden="true"></span>
                <?php eT("Send email invitation"); ?>
            </a>
        </li>

        <!-- Send email reminder -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/email/action/remind/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-mail-volume-fill" aria-hidden="true"></span>
                <?php eT("Send email reminder"); ?>
            </a>
        </li>

        <!-- Edit email template -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/emailtemplates/sa/index/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-mail-line" aria-hidden="true"></span>
                <?php eT("Edit email templates"); ?>
            </a>
        </li>
    <?php endif; ?>

    <li role="presentation" class="dropdown-divider" aria-hidden="true"></li>

    <!-- Bounce processing -->
    <?php if ($hasTokensUpdatePermission):?>
        <?php if($oSurvey->bounceprocessing != 'N' ||  ($oSurvey->bounceprocessing == 'G' && App()->getConfig('bounceaccounttype') != 'off')):?>
            <?php if (function_exists('imap_open')):?>
                <li role="none">
                    <a class="dropdown-item" role="menuitem" href="#" id="startbounceprocessing" data-url="<?php echo Yii::App()->createUrl("admin/tokens/sa/bounceprocessing/surveyid/$oSurvey->sid"); ?>" >
                        <span class="ri-tools-fill" aria-hidden="true"></span>
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
        <li role="none" class="disabled">
            <span class="d-inline-block w-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title='<?php echo $eMessage; ?>'>
                <button type="button" class="dropdown-item" disabled style="pointer-events: none;">
                    <span class="ri-tools-fill" aria-hidden="true"></span>
                    <?php eT("Start bounce processing"); ?>
                </button>
            </span>
        </li>
    <?php endif;?>

    <!-- Bounce settings -->
    <li role="none">
        <a class="dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/bouncesettings/surveyid/$oSurvey->sid"); ?>" >
            <span class="ri-settings-5-fill" aria-hidden="true"></span>
            <?php eT("Bounce settings"); ?>
        </a>
    </li>
</ul>
