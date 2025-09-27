<?php

/**
 * This view render the main menu bar, with configuration menu
 * @var $sitename
 * @var $activesurveyscount
 * @var $dataForConfigMenu
 * @var array $extraMenus   //menu items fetched from plugins
 */
?>

<!-- admin menu bar -->
<nav class="navbar navbar-expand-md">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#small-screens-menus"
            aria-controls="small-screens-menus" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="<?php echo $this->createUrl("/admin/"); ?>">
            <img src="<?= Yii::app()->baseUrl ?>/assets/images/logo-icon-white.png" height="34"
                class="d-inline-block align-bottom" alt="">
            <?= $sitename ?>
        </a>
        <!-- Only on xs screens -->
        <div class="collapse navbar-collapse " id="small-screens-menus">
            <ul class="nav navbar-nav">
                <!-- active surveys -->
                <?php if ($activesurveyscount > 0): ?>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?php echo $this->createUrl('surveyAdministration/listsurveys/active/Y'); ?>">
                            <?php eT("Active surveys"); ?> <span class="badge"><?php echo $activesurveyscount ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <!-- List surveys -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $this->createUrl("surveyAdministration/listsurveys"); ?>">
                        <?php eT("List surveys"); ?>
                    </a>
                </li>
                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>">
                        <?php eT("Logout"); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- Maintenance mode -->
                <?php $sMaintenanceMode = getGlobalSetting('maintenancemode');
                if ($sMaintenanceMode === 'hard' || $sMaintenanceMode === 'soft') { ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?php echo $this->createUrl("admin/globalsettings"); ?>"
                            title="<?php eT("Click here to change maintenance mode setting."); ?>">
                            <span class="ri-alert-fil"></span>
                            <?php eT("Maintenance mode is active!"); ?>
                        </a>
                    </li>
                <?php } ?>

                <!-- Prepended extra menus from plugins -->
                <?php $this->renderPartial("application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'middleSection' => true, 'prependedMenu' => true]); ?>

                <!--
                <li class="nav-item">
                    <a href="<?php echo $this->createUrl("surveyAdministration/newSurvey"); ?>" class="nav-link">
                        <button type="button" class="btn btn-info btn-create" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" title="<?= gT('Create survey') ?>">
                            <i class="ri-add-line"></i>
                        </button>
                    </a>
                </li>
                -->

                <!-- Surveys menus -->

                <li class="nav-item d-flex"><a
                        href="<?php echo $this->createUrl("surveyAdministration/listsurveys"); ?>"
                        class="nav-link ps-0"><?php eT("Surveys"); ?></a>
                    <?php if ($activesurveyscount > 0): ?>
                        <a class="nav-link ps-0 active-surveys"
                            href="<?php echo $this->createUrl('surveyAdministration/listsurveys/active/Y'); ?>">
                            <span class="visually-hidden"><?php eT("View active surveys:"); ?></span>
                            <span class="badge"><?php echo $activesurveyscount ?></span>
                        </a>
                    <?php endif; ?>
                </li>


                <!-- Help menu -->
                <?php $this->renderPartial("/admin/super/_help_menu", []); ?>

                <!-- Configuration menu -->
                <?php $this->renderPartial("/admin/super/_configuration_menu", $dataForConfigMenu); ?>


                <!-- Extra menus from plugins -->
                <?php $this->renderPartial("application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'middleSection' => true, 'prependedMenu' => false]); ?>
            </ul>
        </div>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="nav navbar-nav">
                <!-- Extra menus from plugins -->
                <?php $this->renderPartial("application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'middleSection' => false, 'prependedMenu' => true]); ?>
                <!-- Admin notification system -->
                <?php echo $adminNotifications; ?>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"
                        role="button" aria-expanded="false">
                        <!-- <i class="ri-user-fill"></i> <?php echo Yii::app()->session['user']; ?> <span class="caret"></span></a> -->
                        <span class='rounded-circle text-center d-flex align-items-center justify-content-center me-1'>
                            <?= strtoupper(substr((string) Yii::app()->session['user'], 0, 1)) ?>
                        </span>
                        <?= Yii::app()->session['user']; ?>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-end" role="menu">
                        <li id="admin-menu-item-account">
                            <a class="dropdown-item"
                                href="<?php echo $this->createUrl("/admin/user/sa/personalsettings"); ?>">
                                <?php eT("Account"); ?>
                            </a>
                        </li>

                        <li class="dropdown-divider"></li>

                        <!-- Logout -->
                        <li>
                            <a class="dropdown-item"
                                href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>">
                                <?php eT("Logout"); ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Extra menus from plugins -->
                <?php $this->renderPartial("application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'middleSection' => false, 'prependedMenu' => false]); ?>
            </ul>
        </div><!-- /.nav-collapse -->

    </div>
</nav>
<script type="text/javascript">
    //show tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    })

    $(document).ajaxComplete(function (handler) {
        window.LS.doToolTip();
    });
</script>