<?php
  //todo: this one comes from old TopbarWidget
?>

<ul class="dropdown-menu">
    <?php if ($hasDeletePermission): ?>

        <!-- Delete survey -->
        <li>
            <a class="dropdown-item"
               href="<?php echo App()->createUrl("surveyAdministration/delete/", ['iSurveyID' => $sid]); ?>">
                <span class="ri-delete-bin-fill text-danger"></span>
                <?php eT("Delete survey"); ?>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($hasSurveyTranslatePermission): ?>
        <!-- surveytranslate -->

        <?php if ($hasAdditionalLanguages): ?>

            <!-- Quick-translation -->
            <li>
                <a class="dropdown-item"
                   href="<?= App()->createUrl("quickTranslation/index/surveyid/{$oSurvey->sid}"); ?>">
                    <span class="ri-global-line"></span>
                    <?php eT("Quick-translation"); ?>
                </a>
            </li>

        <?php else: ?>

            <!-- Quick-translation disabled -->
            <li class="disabled">
                <a class="dropdown-item" href="#" class="btntooltip disabled" data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   title="<?php eT('Currently there are no additional languages configured for this survey.'); ?>">
                    <span class="ri-global-line"></span>
                    <?php eT("Quick-translation"); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasSurveyContentPermission): ?>
        <?php if ($conditionsCount > 0): ?>
            <li>
                <!-- condition -->
                <a class="dropdown-item"
                   href="<?php echo App()->createUrl("/admin/conditions/sa/index/subaction/resetsurveylogic/surveyid/{$oSurvey->sid}"); ?>">
                    <span class="icon-resetsurveylogic"></span>
                    <?php eT("Reset conditions"); ?>
                </a>
            </li>
        <?php else : ?>
            <li class="disabled">
                <!-- condition disabled -->
                <a class="dropdown-item" href="#" class="btntooltip disabled" data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   title="<?php eT("Currently there are no conditions configured for this survey.", "js"); ?>">
                    <span class="icon-resetsurveylogic"></span>
                    <?php eT("Reset conditions"); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasSurveyReadPermission): ?>
        <!-- survey content -->

        <?php if (count($oSurvey->allLanguages) == 1): ?>
            <!-- one language -->

            <!-- Survey logic file -->
            <li>
                <a class="dropdown-item"
                   href='<?php echo App()->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/"); ?>'>
                    <span class="icon-expressionmanagercheck"></span>
                    <?php eT("Survey logic file"); ?>
                </a>
            </li>
        <?php else : ?>
            <!-- multilangue  -->

            <li role="separator" class="dropdown-divider"></li>

            <!-- Survey logic file -->
            <li class="dropdown-header"><?php eT("Survey logic file"); ?></li>

            <!-- Base language -->
            <li>
                <a class="dropdown-item"
                    href='<?php echo App()->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/lang/$oSurvey->language"); ?>'>
                    <span class="icon-expressionmanagercheck"></span>
                    <?php echo getLanguageNameFromCode($oSurvey->language, false); ?>
                </a>
            </li>

            <!-- Additional languages -->
            <?php if (count($oSurvey->additionalLanguages) <= 2): ?>
                <?php foreach ($oSurvey->additionalLanguages as $tmp_lang): ?>
                    <li>
                        <a class="dropdown-item"
                            href='<?php echo App()->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/lang/$tmp_lang"); ?>'>
                            <span class="icon-expressionmanagercheck"></span>
                            <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                    $languageOptions = [];
                    foreach ($oSurvey->additionalLanguages as $language) {
                        $languageOptions[] = [
                            'key' => $language,
                            'href' => Yii::app()->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/lang/$language"),
                            'text' => getLanguageNameFromCode($language, false),
                        ];
                    }

                    $languagesModal = $this->widget('ext.OptionsModalWidget.OptionsModalWidget', [
                        'modalTitle' => gT("Select language to show Survey Logic File"),
                        'options' => $languageOptions
                    ]);
                    $languagesModalId = $languagesModal->getModalId();
                ?>
                <li>
                    <a class="dropdown-item" data-bs-toggle="modal" href="#<?= $languagesModalId ?>">
                        <span class="icon-expressionmanagercheck"></span> <?= gT("Other languages"); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$oSurvey->isActive && $hasSurveyContentPermission): ?>
        <li role="separator" class="dropdown-divider"></li>

        <!-- Regenerate question codes -->
        <li class="dropdown-header">
            <?php eT("Regenerate question codes"); ?>
        </li>

        <!-- Straight -->
        <li>
            <a class="dropdown-item"
               href="<?php echo App()->createUrl("/surveyAdministration/regenerateQuestionCodes/surveyid/{$sid}/subaction/straight"); ?>">
                <span class="icon-resetsurveylogic"></span>
                <?php eT("Straight"); ?>
            </a>
        </li>

        <!-- By question group -->
        <li>
            <a class="dropdown-item"
               href="<?php echo App()->createUrl("/surveyAdministration/regenerateQuestionCodes/surveyid/{$sid}/subaction/bygroup"); ?>">
                <span class="icon-resetsurveylogic"></span>
                <?php eT("By question group"); ?>
            </a>
        </li>
    <?php endif; ?>

    <?php if (!empty($extraToolsMenuItems)): ?>
        <?php foreach ($extraToolsMenuItems as $menuItem): ?>
            <?php if ($menuItem->isDivider()): ?>
                <li class="dropdown-divider"></li>
            <?php elseif ($menuItem->isSmallText()): ?>
                <li class="dropdown-header"><?php echo $menuItem->getLabel(); ?></li>
            <?php else: ?>
                <li>
                    <a class="dropdown-item" href="<?php echo $menuItem->getHref(); ?>">
                        <!-- Spit out icon if present -->
                        <?php if ($menuItem->getIconClass() != ''): ?>
                            <span class="<?php echo $menuItem->getIconClass(); ?>">&nbsp;</span>
                        <?php endif; ?>
                        <?php echo $menuItem->getLabel(); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

</ul>
