<!-- Add new token entry -->
<ul class="dropdown-menu">
    <?php if ($hasTokensCreatePermission): ?>
        <li>
            <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/tokens/sa/addnew/surveyid/$oSurvey->sid"); ?>" >
                <span class="ri-add-circle-fill"></span>
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
                <span class="ri-download-2-fill"></span>
                <?php eT("CSV file"); ?>
            </a>
        </li>

        <!-- from LDAP query -->
        <li>
            <a class="pjax dropdown-item"  href="<?php echo Yii::App()->createUrl("admin/tokens/sa/importldap/surveyid/$oSurvey->sid") ?>" >
                <span class="ri-download-2-fill"></span>
                <?php eT("LDAP query"); ?>
            </a>
        </li>
    <?php endif; ?>
</ul>
