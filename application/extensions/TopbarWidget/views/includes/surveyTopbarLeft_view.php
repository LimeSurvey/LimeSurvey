<?php
/**
 * Left side buttons for general Survey Topbar
 */
?>
<!-- survey activation -->
<?php if (!$oSurvey->isActive): ?>
    <!-- activate -->
    <?php if ($canactivate): ?>
        <a id='ls-activate-survey' class="btn btn-success"
           href="<?php echo App()->createUrl("surveyAdministration/activate/", ['iSurveyID' => $sid]); ?>"
           role="button">
            <?php eT("Activate this survey"); ?>
        </a>
        <!-- can't activate -->
    <?php else: ?>
        <span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom"
              title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button id='ls-activate-survey' type="button" class="btn btn-success btntooltip" disabled="disabled">
                <?php eT("Activate this survey"); ?>
            </button>
        </span>
    <?php endif; ?>
<?php else : ?>

    <!-- activate expired survey -->
    <?php if ($expired):
        // TODO: ToolTip for expired survey
    elseif ($notstarted):
        // TODO: ToolTip for not started survey
    endif; ?>

    <!-- Stop survey -->
    <?php if ($canactivate): ?>
        <a class="btn btn-danger btntooltip"
           href="<?php echo App()->createUrl("surveyAdministration/deactivate/", ['iSurveyID' => $sid]); ?>">
            <i class="ri-stop-circle-fill"></i>
            <?php eT("Stop this survey"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Preview/Run survey -->
<?php if ($hasSurveyContentPermission) : ?>
    <!-- Multinlinguage -->
    <?php if (count($oSurvey->allLanguages) > 1): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <?php if ($oSurvey->active == 'N'): ?>
                    <span class="ri-eye-fill"></span>
                    <?php eT('Preview survey'); ?>
                <?php else: ?>
                    <span class="ri-play-fill"></span>
                    <?php eT('Run survey'); ?>
                <?php endif; ?>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                    <li>
                        <a class="dropdown-item" target='_blank' id='<?= $contextbutton ?>_button_<?= $tmp_lang ?>'
                           href='<?php echo App()->createUrl("survey/index", array('sid' => $oSurvey->sid, 'newtest' => "Y", 'lang' => $tmp_lang)); ?>'>
                            <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- uniq language -->
    <?php else: ?>
        <a class="btn btn-outline-secondary btntooltip" id='<?= $contextbutton ?>_button'
           href="<?php echo App()->createUrl("survey/index", array('sid' => $oSurvey->sid, 'newtest' => "Y", 'lang' => $oSurvey->language)); ?>"
           role="button" accesskey='d' target='_blank'>
            <?php if ($oSurvey->active == 'N'): ?>
                <span class="ri-eye-fill"></span>
                <?php eT('Preview survey'); ?>
            <?php else: ?>
                <span class="ri-play-fill"></span>
                <?php eT('Run survey'); ?>
            <?php endif; ?>
            <i class="icon  ri-external-link-fill"></i>
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if ($showToolsMenu): ?>
    <!-- Tools  -->
    <div class="btn-group ">

        <!-- Main button dropdown -->
        <button id="ls-tools-button" type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="ri-tools-fill"></span>
            <?php eT('Tools'); ?>&nbsp;<span class="caret"></span>
        </button>

        <!-- dropdown -->
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
                            <span class="ri-survey-fill"></span>
                            <?php eT("Reset conditions"); ?>
                        </a>
                    </li>
                <?php else : ?>
                    <li class="disabled">
                        <!-- condition disabled -->
                        <a class="dropdown-item" href="#" class="btntooltip disabled" data-bs-toggle="tooltip"
                           data-bs-placement="bottom"
                           title="<?php eT("Currently there are no conditions configured for this survey.", "js"); ?>">
                            <span class="ri-survey-fill"></span>
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
                            <span class="ri-checkbox-fill"></span>
                            <?php eT("Survey logic file"); ?>
                        </a>
                    </li>
                <?php else : ?>
                    <!-- multilangue  -->

                    <li role="separator" class="dropdown-divider"></li>

                    <!-- Survey logic file -->
                    <li class="dropdown-header"><?php eT("Survey logic file"); ?></li>
                    <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                        <!-- Languages -->

                        <li>
                            <a class="dropdown-item"
                               href='<?php echo App()->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/lang/$tmp_lang"); ?>'>
                                <span class="ri-checkbox-fill"></span>
                                <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
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
                        <span class="ri-survey-fill"></span>
                        <?php eT("Straight"); ?>
                    </a>
                </li>

                <!-- By question group -->
                <li>
                    <a class="dropdown-item"
                       href="<?php echo App()->createUrl("/surveyAdministration/regenerateQuestionCodes/surveyid/{$sid}/subaction/bygroup"); ?>">
                        <span class="ri-survey-fill"></span>
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
    </div>
<?php endif; ?>

<?php if (!empty($beforeSurveyBarRender)): ?>
    <?php foreach ($beforeSurveyBarRender as $menu): ?>
        <div class='btn-group'>
            <?php if ($menu->isDropDown()): ?>
                <button class="dropdown-toggle btn btn-outline-secondary" data-bs-toggle="dropdown" href="#">
                    <?php if ($menu->getIconClass()): ?>
                        <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
                    <?php endif; ?>
                    <?php echo $menu->getLabel(); ?>
                    &nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($menu->getMenuItems() as $menuItem): ?>
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
                </ul>
            <?php else : ?>
                <a class='btn btn-outline-secondary' href="<?php echo $menu->getHref(); ?>">
                    <?php if ($menu->getIconClass()): ?>
                        <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
                    <?php endif; ?>
                    <?php echo $menu->getLabel(); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Export -->
<?php if (Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export')): ?>
    <?php App()->getController()->renderPartial(
        '/admin/survey/surveybar_displayexport',
        [
            'hasResponsesExportPermission' => $hasResponsesExportPermission,
            'hasTokensExportPermission' => $hasSurveyTokensExportPermission,
            'hasSurveyExportPermission' => $hasSurveyExportPermission,
            'oSurvey' => $oSurvey,
            'onelanguage' => (count($oSurvey->allLanguages) == 1)
        ]
    ); ?>
<?php endif; ?>
