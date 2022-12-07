<?php
/**
 * This view render the main menu bar, with configuration menu
 * @var $sitename
 * @var $activesurveyscount
 * @var $dataForConfigMenu
 */
?>

<!-- admin menu bar -->
<nav class="navbar navbar-expand-md">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#small-screens-menus" aria-controls="small-screens-menus" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="<?php echo $this->createUrl("/admin/"); ?>">
            <img id="nav-logo" alt="logo" src="/themes/admin/Sea_Green/images/logo.png" class="d-inline-block">
        </a>
        <!-- Only on xs screens -->
        <div class="collapse navbar-collapse " id="small-screens-menus">
            <ul class="nav navbar-nav">
                <!-- active surveys -->
                <?php if ($activesurveyscount > 0): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->createUrl('surveyAdministration/listsurveys/active/Y'); ?>">
                            <?php eT("Active surveys"); ?> <span class="badge rounded-pill"><?php echo $activesurveyscount ?></span>
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

        <div class="collapse navbar-collapse justify-content-end">
            <ul class="nav navbar-nav">
                <!-- Maintenance mode -->
                <?php $sMaintenanceMode = getGlobalSetting('maintenancemode');
                if ($sMaintenanceMode === 'hard' || $sMaintenanceMode === 'soft') { ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?php echo $this->createUrl("admin/globalsettings"); ?>" title="<?php eT("Click here to change maintenance mode setting."); ?>">
                            <span class="fa fa-warning"></span>
                            <?php eT("Maintenance mode is active!"); ?>
                        </a>
                    </li>
                <?php } ?>

                <!-- Prepended extra menus from plugins -->
                <?php $this->renderPartial( "application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'prependedMenu' => true]); ?>

                <!-- create survey -->
                <li class="nav-item">
                    <a href="<?php echo $this->createUrl("surveyAdministration/newSurvey"); ?>" class="nav-link">
                        <!-- <i class="ri-add-circle-fill"></i> -->
                        <?php eT("Create survey"); ?>
                    </a>
                </li>
                <!-- Surveys menus -->
                <li class="dropdown-split-left nav-item">
                    <a href="<?php echo $this->createUrl("surveyAdministration/listsurveys"); ?>" class="nav-link">
                        <!-- <i class="ri-list-check"></i> -->
                        <?php eT("Surveys"); ?>
                    </a>
                </li>

                <!-- Help menu -->
                <?php $this->renderPartial("/admin/super/_help_menu", []); ?>

                <!-- Configuration menu -->
                <?php $this->renderPartial("/admin/super/_configuration_menu", $dataForConfigMenu); ?>


                <!-- user menu -->
                <!-- active surveys -->
                <?php if ($activesurveyscount > 0): ?>
                    <li class="nav-item">
                        <a href="<?php echo $this->createUrl('surveyAdministration/listsurveys/active/Y'); ?>" class="nav-link">
                            <?php eT("Active surveys"); ?> <span class="badge rounded-pill"> <?php echo $activesurveyscount ?> </span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Extra menus from plugins -->
                <?php $this->renderPartial( "application.libraries.MenuObjects.views._extraMenu", ['extraMenus' => $extraMenus, 'prependedMenu' => false]); ?>

                <!-- Admin notification system -->
                <?php echo $adminNotifications; ?>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <!-- <i class="ri-user-fill"></i> <?php echo Yii::app()->session['user']; ?> <span class="caret"></span></a> -->
<!--                         @TODO remove inline style and put it into corresponding SCSS file. This is here just for demo purpose -->
                        <span class='badge rounded-pill' style="background-color: #3BFFB7; color: #25003E;">
                            <?= strtoupper(substr(Yii::app()->session['user'], 0, 1)) ?>
                        </span>
                        <?= Yii::app()->session['user']; ?>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-end" role="menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo $this->createUrl("/admin/user/sa/personalsettings"); ?>">
                                <?php eT("My account"); ?>
                            </a>
                        </li>

                        <li class="dropdown-divider"></li>

                        <!-- Logout -->
                        <li>
                            <a class="dropdown-item" href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>">
                                <?php eT("Logout"); ?>
                            </a>
                        </li>
                    </ul>
                </li>

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

    $( document ).ajaxComplete(function(handler) {
        window.LS.doToolTip();
    });
</script>
