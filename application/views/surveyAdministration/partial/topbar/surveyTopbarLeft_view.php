<?php

/**
 * Left side buttons for general Survey Topbar
 *
 */

App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'activatesurvey.js',
    LSYii_ClientScript::POS_BEGIN
);

?>

<?php if ($showToolsMenu) {
    $toolsDropDownItems = $this->renderPartial(
        '/surveyAdministration/partial/topbar/surveyToolsDropdownItems',
        get_defined_vars(),
        true
    ); ?>
    <!-- Tools  -->
    <!-- Main button dropdown -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Tools'),
        'isDropDown' => true,
        'dropDownContent' => $toolsDropDownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
<?php } ?>
<!-- survey activation -->
<?php if (!$oSurvey->isActive) : ?>
    <!-- activate -->
    <?php
    $htmlOptions = [
        'class' => 'btn btn-primary btntooltip',
        'role' => 'button',
        'data-surveyid' => $sid,
        'data-url' => Yii::app()->createUrl('surveyAdministration/activateSurvey'),
        'onclick' => 'openModalActivate();'
    ];
    ?>
    <?php if (!$canactivate) : ?>
        <span class="btntooltip" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom"
        title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
        <?php $htmlOptions['disabled'] = 'disabled'; ?>
    <?php endif; ?>
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-activate-survey',
            'id' => 'ls-activate-survey', // --> used in js to trigger show modal
            'text' => gT('Activate survey'),
            'icon' => 'ri-check-fill',
            //'link' => App()->createUrl("surveyAdministration/activate/", ['iSurveyID' => $sid]),
            'htmlOptions' => $htmlOptions,
        ]); ?>
    <?php if (!$canactivate) : ?>
        </span>
    <?php endif; ?>
<?php else : ?>
    <!-- Stop survey -->
    <?php if ($candeactivate) : ?>
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'stop-survey',
            'text' => gT('Stop this survey'),
            'icon' => 'ri-stop-circle-fill',
            'link' => App()->createUrl("surveyAdministration/deactivate/", ['iSurveyID' => $sid]),
            'htmlOptions' => [
                    'class' => 'btn btn-danger btntooltip'
            ],
        ]); ?>
    <?php endif; ?>
<?php endif; ?>

<!-- Preview/Run survey -->
<?php
if ($hasSurveyContentPermission) {
    $this->renderPartial(
        '/surveyAdministration/partial/topbar/previewOrRunButton_view',
        [
            'survey' => $oSurvey,
            'surveyLanguages' => $surveyLanguages,
            'id' => $contextbutton . '_button',
            'name' => $contextbutton . '_button',
            ]
    );
}
?>

<?php if (!empty($beforeSurveyBarRender)) { ?>
<!--@TODO adjust to new theme-->
    <?php foreach ($beforeSurveyBarRender as $menu) { ?>
        <div class='btn-group'>
            <?php if ($menu->isDropDown()) : ?>
                <button class="dropdown-toggle btn btn-outline-secondary" data-bs-toggle="dropdown" href="#">
                    <?php if ($menu->getIconClass()) : ?>
                        <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
                    <?php endif; ?>
                    <?php echo $menu->getLabel(); ?>
                    &nbsp;
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($menu->getMenuItems() as $menuItem) { ?>
                        <?php if ($menuItem->isDivider()) : ?>
                            <li class="dropdown-divider"></li>
                        <?php elseif ($menuItem->isSmallText()) : ?>
                            <li class="dropdown-header"><?php echo $menuItem->getLabel(); ?></li>
                        <?php else: ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo $menuItem->getHref(); ?>">
                                    <!-- Spit out icon if present -->
                                    <?php if ($menuItem->getIconClass() != '') : ?>
                                        <span class="<?php echo $menuItem->getIconClass(); ?>">&nbsp;</span>
                                    <?php endif; ?>
                                    <?php echo $menuItem->getLabel(); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php } ?>
                </ul>
            <?php else : ?>
                <a class='btn btn-outline-secondary' href="<?php echo $menu->getHref(); ?>">
                    <?php if ($menu->getIconClass()) : ?>
                        <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
                    <?php endif; ?>
                    <?php echo $menu->getLabel(); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php }  //end foreach?>
<?php } ?>

<!-- Export -->
<?php
if (Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export')) {
    App()->getController()->renderPartial(
        '/admin/survey/surveybar_displayexport',
        [
            'hasResponsesExportPermission' => $hasResponsesExportPermission,
            'hasTokensExportPermission' => $hasSurveyTokensExportPermission,
            'hasSurveyExportPermission' => $hasSurveyExportPermission,
            'oSurvey' => $oSurvey,
            'onelanguage' => (count($oSurvey->allLanguages) == 1)
        ]
    );
}



?>

