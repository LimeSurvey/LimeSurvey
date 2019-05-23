<?php
/**
* Configuration menu. rendered from adminmenu
* @var $userscount
*/

//Todo : move to controller
?>

<!-- Configuration -->
<?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')
    || Permission::model()->hasGlobalPermission('templates', 'read')
    || Permission::model()->hasGlobalPermission('labelsets', 'read')
    || Permission::model()->hasGlobalPermission('users', 'read')
    || Permission::model()->hasGlobalPermission('usergroups', 'read')
    || Permission::model()->hasGlobalPermission('participantpanel', 'read')
    || Permission::model()->hasGlobalPermission('participantpanel', 'create')
    || Permission::model()->hasGlobalPermission('participantpanel', 'update')
    || Permission::model()->hasGlobalPermission('participantpanel', 'delete')
    || ParticipantShare::model()->exists('share_uid = :userid', [':userid' => App()->user->id])
    || Permission::model()->hasGlobalPermission('settings', 'read')
): ?>


<li class="dropdown mega-dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="icon-settings" ></span>
        <?php eT('Configuration');?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu mega-dropdown-menu" id="mainmenu-dropdown">

        <!-- System overview -->
        <li class="col-sm-3">

            <!-- System overview -->
            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')): ?>
                <div class="box" id="systemoverview">
                    <div class="box-icon">
                        <span class="fa fa-info-sign" id="info-header"></span>
                    </div>
                    <div class="info">
                        <div class="text-center"><?php eT("System overview"); ?></div>
                        <dl class="dl-horizontal">
                            <dt class="text-info"><?php eT('Users');?></dt>
                            <dd><?php echo $userscount;?></dd>
                            <dt class="text-info"><?php eT('Surveys');?></dt>
                            <dd><?php echo $surveyscount; ?></dd>
                            <dt class="text-info"><?php eT('Active surveys');?></dt>
                            <dd><?php echo $activesurveyscount; ?></dd>
                            <dt class="text-info"><?php eT('ComfortUpdate key');?></dt>
                            <dd><?php echo $comfortUpdateKey;?></dd>

                        </dl>
                    </div>
                </div>
            <?php endif; ?>
        </li>

        <!-- Expression Manager -->
        <li class="col-sm-3">
            <?php if(YII_DEBUG):?>
                <ul>

                    <!-- Expression Manager -->
                    <li class="dropdown-header">
                        <span class="icon-expression" ></span>
                        <?php eT("Expression Manager");?>
                    </li>

                    <!-- Expression Manager Descriptions -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/expressions"); ?>">
                            <?php eT("Expression Manager descriptions");?>
                        </a>
                    </li>

                    <!--Available Functions -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>">
                            <?php eT("Available functions");?>
                        </a>
                    </li>

                    <!--Unit Tests of Expressions Within Strings -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>">
                            <?php eT("Unit tests of expressions within strings");?>
                        </a>
                    </li>

                    <!-- Unit Test Dynamic Relevance Processing -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/relevance'); ?>">
                            <?php eT("Unit test dynamic relevance processing");?>
                        </a>
                    </li>

                    <!-- Preview Conversion of Conditions to Relevance -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/conditions2relevance'); ?>">
                            <?php eT("Preview conversion of conditions to relevance");?>
                        </a>
                    </li>

                    <!-- Bulk Convert Conditions to Relevance -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/upgrade_conditions2relevance'); ?>">
                            <?php eT("Bulk convert conditions to relevance");?>
                        </a>
                    </li>

                    <!-- Test Navigation -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/navigation_test'); ?>">
                            <?php eT("Test navigation");?>
                        </a>
                    </li>

                    <!-- Show Survey logic file -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_form'); ?>">
                            <?php eT("Show survey logic file");?>
                        </a>
                    </li>
                </ul>
                <?php endif;?>
        </li>

        <!-- Advanced -->

        <li class="col-sm-2">
            <ul>

                <!-- Advanced -->
                <li class="dropdown-header">
                    <span class="icon-tools" ></span>
                    <?php eT('Advanced');?>
                </li>
                <?php if(Permission::model()->hasGlobalPermission('templates','read')): ?>
                    <!-- Theme Editor -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/themeoptions"); ?>">
                            <?php eT("Themes");?>
                        </a>
                    </li>
                    <?php endif;?>
                <?php if(Permission::model()->hasGlobalPermission('labelsets','read')): ?>
                    <!-- Edit label sets -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>">
                            <?php eT("Manage label sets");?>
                        </a>
                    </li>
                    <?php endif;?>

                <!-- Check Data Integrity -->
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')): ?>

                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/checkintegrity"); ?>">
                            <?php eT("Check data integrity");?>
                        </a>
                    </li>

                    <!-- Backup Entire Database -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/dumpdb"); ?>">
                            <?php eT("Backup entire database");?>
                        </a>
                    </li>

                <?php endif;?>

                <!-- Comfort update -->
                <?php if(Permission::model()->hasGlobalPermission('superadmin')): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/update"); ?>">
                            <?php eT("ComfortUpdate");?>
                        </a>
                    </li>
                <?php endif;?>
            </ul>

        </li>

        <!-- Users -->
        <li class="col-sm-2">

            <!-- Users -->
            <ul>

                <!-- Users -->
                <li class="dropdown-header">

                    <span class="icon-user" ></span>
                    <?php eT('Users');?>
                </li>

                <!-- Manage survey administrators -->
                <?php if(Permission::model()->hasGlobalPermission('users','read')): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/user/sa/index"); ?>">
                            <?php eT("Manage survey administrators");?>
                        </a>
                    </li>
                    <?php endif;?>
                <?php if(Permission::model()->hasGlobalPermission('usergroups','read')): ?>

                    <!-- Create/edit user groups -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/usergroups/sa/index"); ?>">
                            <?php eT("Create/edit user groups");?>
                        </a>
                    </li>

                    <?php endif;?>

                <!-- Central participant database -->
                <?php if (Permission::model()->hasGlobalPermission('participantpanel', 'read')
                    || Permission::model()->hasGlobalPermission('participantpanel', 'create')
                    || Permission::model()->hasGlobalPermission('participantpanel', 'update')
                    || Permission::model()->hasGlobalPermission('participantpanel', 'delete')
                    || ParticipantShare::model()->exists('share_uid = :userid', [':userid' => App()->user->id])
                ): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
                            <?php eT("Central participant database"); ?>
                        </a>
                    </li>
                    <?php endif;?>
            </ul>
        </li>



        <!-- Settings -->

        <li class="col-sm-2">
            <ul>

                <!-- Settings -->
                <li class="dropdown-header">
                    <span class="icon-global" ></span>
                    <?php eT('Settings');?>
                </li>

                <?php if(Permission::model()->hasGlobalPermission('settings','read')): ?>
                    <!-- Home page settings -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/homepagesettings"); ?>">
                            <?php eT("Home page settings");?>
                        </a>
                    </li>

                    <!-- Global settings -->
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/globalsettings"); ?>">
                            <?php eT("Global settings");?>
                        </a>
                    </li>

                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("/admin/pluginmanager/sa/index"); ?>">
                            <?php eT("Plugin manager");?>
                        </a>
                    </li>
                <!-- Surveymenu Editor -->
                <?php if(Permission::model()->hasGlobalPermission('settings','read')): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/menus/sa/view"); ?>">
                            <?php eT("Menu configuration");?>
                        </a>
                    </li>
                <?php endif;?>
                <!-- Surveymenu entry Editor -->
                <?php if(Permission::model()->hasGlobalPermission('settings','read')): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo $this->createUrl("admin/menuentries/sa/view"); ?>">
                            <?php eT("Menu entries configuration");?>
                        </a>
                    </li>
                <?php endif;?>

                <?php endif;?>

            </ul>
        </li>
    </ul>
</li>
<?php endif;?>
