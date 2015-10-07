<?php
/**
 * This view render the main menu bar, with configuration menu
 * @var $sitename
 * @var $activesurveyscount
 * @var $dataForConfigMenu
 */
?>

<!-- admin menu bar -->
<nav class="navbar">
  <div class="navbar-header">
      <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="<?php echo $this->createUrl("/admin/"); ?>">
            <?php echo $sitename; ?>
        </a>
    </div>

    <div class="collapse navbar-collapse js-navbar-collapse">

        <!-- Left -->
        <ul class="nav navbar-nav">

        </ul>

        <!-- Right -->
        <ul class="nav navbar-nav navbar-right">

            <!-- Configuration menu -->
            <?php $this->renderPartial( "/admin/super/_configuration_menu", $dataForConfigMenu ); ?>

            <!-- Surveys menus -->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  <?php eT("Surveys");?> <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu">

                <!-- Create a new survey -->
                <li>
                    <a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey"); ?>">
                        <?php eT("Create a new survey");?>
                    </a>
                </li>
                <li class="divider"></li>

                <!-- List surveys -->
                <li>
                    <a href="<?php echo $this->createUrl("admin/survey/sa/listsurveys"); ?>">
                        <?php eT("List surveys");?>
                    </a>
                </li>
              </ul>
            </li>

            <!-- user menu -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" ><?php echo Yii::app()->session['user'];?> <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">

                    <!-- Edit your personal preferences -->
                    <li>
                        <a href="<?php echo $this->createUrl("/admin/user/sa/personalsettings"); ?>"><?php eT("Edit your personal preferences");?></a>
                    </li>

                    <li class="divider"></li>

                    <!-- Logout -->
                    <li>
                        <a href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>">
                            <?php eT("Logout");?>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- active surveys -->
            <?php if ($activesurveyscount > 0): ?>
                <li>
                    <a href="<?php echo $this->createUrl('admin/survey/sa/listsurveys/active/Y');?>">
                        <?php echo $activesurveyscount; ?> active surveys
                    </a>
                </li>
            <?php endif;?>

            <li class="nav-icon-btn nav-icon-btn-danger dropdown">
                <a href="#notifications" class="dropdown-toggle" data-toggle="dropdown">
                    <?php if($showupdate): ?>
                        <span class=" label update-small-notification <?php if(Yii::app()->session['notificationstate']=='1' || Yii::app()->session['unstable_update'] ){echo 'hidden';};?>" >1</span>
                    <?php endif;?>
                    <i class="nav-icon fa fa-bullhorn"></i>
                </a>

                <!-- NOTIFICATIONS -->
                <?php if($showupdate): ?>
                <ul class="dropdown-menu update-small-notification <?php if(Yii::app()->session['notificationstate']=='1' || Yii::app()->session['unstable_update'] ){echo 'hidden';};?>" role="menu">
                    <li class="notifications-list " id="main-navbar-notifications" >
                        <strong><?php eT("a new update is available");?> </strong> <a href="<?php echo Yii::app()->createUrl("admin/update"); ?>"><?php eT('Click here to use ComfortUpdate.');?></a>
                    </li>
                </ul> <!-- / .dropdown-menu -->
                <?php endif;?>
            </li>

        </ul>
    </div><!-- /.nav-collapse -->
</nav>
