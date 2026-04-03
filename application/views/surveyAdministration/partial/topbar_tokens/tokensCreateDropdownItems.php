<!-- Add new token entry" -->
<ul class="dropdown-menu" role="menu" aria-label="<?php echo CHtml::encode(gT('Add participants')); ?>">
    <?php if ($hasTokensCreatePermission): ?>
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/addnew/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-add-circle-fill" aria-hidden="true"></span>
                <?php eT("Add participant"); ?>
            </a>
        </li>

        <!-- Create dummy tokens -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/adddummies/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-add-box-fill" aria-hidden="true"></span>
                <?php eT("Create dummy participants"); ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($hasTokensCreatePermission && $hasTokensImportPermission): ?>
        <li role="presentation" class="dropdown-divider" aria-hidden="true"></li>
    <?php endif; ?>
    <!-- Import tokens -->
    <?php if ($hasTokensImportPermission): ?>

        <li role="presentation">
            <h6 class="dropdown-header"><?php eT("Import participants from:"); ?></h6>
        </li>

        <!-- from CSV file -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/import/surveyid/$oSurvey->sid") ?>" >
                <span class="ri-download-2-fill" aria-hidden="true"></span>
                <?php eT("CSV file"); ?>
            </a>
        </li>

        <!-- from LDAP query -->
        <li role="none">
            <a class="pjax dropdown-item" role="menuitem" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/importldap/surveyid/$oSurvey->sid") ?>" >
                <span class="ri-download-2-fill" aria-hidden="true"></span>
                <?php eT("LDAP query"); ?>
            </a>
        </li>
    <?php endif; ?>
</ul>
