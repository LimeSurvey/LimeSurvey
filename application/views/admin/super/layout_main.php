<?php
//All paths relative from /application/views

/**
 * @var SurveyCommonAction $this
 * @var array $aData
 * @var string $content the html of the page to be rendered inside the layout
 *
 * todo: remove this view when all controllers are refactored
 */
//headers will be generated with the template file /admin/super/header.php
$this->showHeaders($aData);
//The adminmenu bar will be generated from /admin/super/adminmenu.php
$this->showadminmenu($aData);
$layoutHelper = new LayoutHelper();
?>
<div id="layout_sidebar">
    <?php App()->getController()->widget('ext.SideBarWidget.SideBarWidget'); ?>
    <div class="container-40">
        <?= $layoutHelper->renderTopbarTemplate($aData) ?>
        <!-- BEGIN LAYOUT_MAIN -->


        <div class='container-fluid'>
            <?= $this->updatenotification() ?>
        </div>

        <?= $this->notifications() ?>

        <!--The load indicator for pjax-->
        <div id="pjax-file-load-container" class="ls-flex-row col-12">
            <div style="height:2px;width:0;"></div>
        </div>

        <?php $containerClass = !App()->user->isGuest ? 'container-fluid' : 'container-fluid ps-0' ?>
        <!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
        <div class="<?= $containerClass ?>" id="in_survey_common_action">
            <?= $content ?>
        </div>
    </div>
</div>
<!-- END LAYOUT_MAIN -->

<?php
// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    App()->getController()->loadEndScripts();
}
?>
<?php if (!App()->user->isGuest) : ?>
    <?php if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) : ?>
        <?= App()->getController()->getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual')) ?>
    <?php endif; ?>
<?php else : ?>
    </body>
    </html>
<?php endif; ?>
