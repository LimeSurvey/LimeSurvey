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
                        <span class="ri-tools-fill"></span>
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
                <span class="ri-tools-fill""></span>
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
