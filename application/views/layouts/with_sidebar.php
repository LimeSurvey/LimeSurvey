<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);
$aData = App()->getController()->aData;
$layoutHelper = new LayoutHelper();
$layoutHelper->showHeaders($aData);
$layoutHelper->showadminmenu($aData);
?>

<!-- BEGIN LAYOUT MAIN (refactored controllers) -->
<?= $layoutHelper->renderTopbarTemplate($aData) ?>
<div class='container-fluid'>
    <?= $layoutHelper->updatenotification() ?>
</div>

<?= $layoutHelper->notifications() ?>

<!-- The load indicator for pjax -->
<div id="pjax-file-load-container" class="ls-flex-row col-12">
    <div style="height:2px;width:0;"></div>
</div>

<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
<div id="layout_sidebar">
    <?php App()->getController()->widget('ext.SideBarWidget.SideBarWidget'); ?>
    <div class="container-40" id="in_survey_common_action">
        <?= $content ?>
    </div>
</div>

<!-- Footer-->
<?php if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    $layoutHelper->loadEndScripts();
} ?>

<?php if (!App()->user->isGuest) : ?>
    <?php if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) : ?>
        <?php $layoutHelper->getAdminFooter('http://manual.gitit-tech.com') ?>
    <?php endif; ?>
<?php else : ?>
    </body>
    </html>
<?php endif; ?>
