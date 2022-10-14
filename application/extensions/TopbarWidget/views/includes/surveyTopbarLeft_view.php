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
            <i class="fa fa-stop-circle"></i>
            <?php eT("Stop this survey"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>


<!-- Preview/Run survey -->
<?php if ($hasSurveyContentPermission) : ?>
    <?php $this->render('includes/previewOrRunButton_view',
        [
            'survey' => $oSurvey,
            'surveyLanguages' => $surveyLanguages,
            'id' => $contextbutton . '_button',
            'name' => $contextbutton . '_button',
            ]); ?>
<?php endif; ?>

<?php if ($showToolsMenu): ?>
    <?php $toolsDropDownItems = $this->render('includes/surveyToolsDropdownItems', get_defined_vars(), true); ?>
    <!-- Tools  -->
    <div class="d-inline-flex ">
        <!-- Main button dropdown -->
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-tools-button',
            'id' => 'ls-tools-button',
            'text' => gT('Tools'),
            'menu' => true,
            'menuContent' => $toolsDropDownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>
    </div>
<?php endif; ?>

<?php if (!empty($beforeSurveyBarRender)): ?>
<!--@TODO adjust to new theme-->
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
