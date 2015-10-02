<?php
/**
 * Configuration menu. rendered from adminmenu
 * @var $userscount
 */
?>

<!-- Configuration -->
<li class="dropdown mega-dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/global.png" /> <?php eT('Configuration');?><span class="caret"></span></a>				
    <ul class="dropdown-menu mega-dropdown-menu" id="mainmenu-dropdown">
        
        <!-- First column -->
        <li class="col-sm-3 ">
            
            <!-- System overview -->
            <div class="box">
                <div class="box-icon">
                    <span class="glyphicon glyphicon-info-sign" id="info-header"></span>
                </div>
                <div class="info">
                    <h5 class="text-center"><?php eT("System overview"); ?></h5>
                    <dl class="dl-horizontal">
                        <dt class="text-info"><?php eT('Users');?></dt>
                        <dd><?php echo $userscount;?></dd>
                        <dt class="text-info"><?php eT('Surveys');?></dt>
                        <dd><?php echo $surveyscount; ?></dd>
                        <dt class="text-info"><?php eT('Active surveys');?></dt>
                        <dd><?php echo $activesurveyscount; ?></dd>
                        <dt class="text-info"><?php eT('Active tokens tables');?></dt>
                        <dd><?php echo $activetokens;?></dd>
                        <dt class="text-info"><?php eT('Deactivated result tables');?></dt>
                        <dd><?php echo $deactivatedsurveys;?></dd>    
                        <dt class="text-info"><?php eT('Deactivated token tables');?></dt>
                        <dd><?php echo $deactivatedtokens;?></dd>
                    </dl>
                </div>
            </div>
        </li>

        <!-- Second column -->
        <li class="col-sm-2">
            
            <!-- Users -->
            <ul>
                
                <!-- Users -->
                <li class="dropdown-header">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/user.png" />
                    <?php eT('Users');?>
                </li>
                
                <!-- Manage survey administrators -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/user/sa/index"); ?>">
                        <?php eT("Manage survey administrators");?>
                    </a>
                </li>
                
                <!-- Create/edit user groups -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/usergroups/sa/index"); ?>">
                        <?php eT("Create/edit user groups");?>
                    </a>
                </li>
                
                <!-- Central participant database/panel -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
                        <?php eT("Central participant database/panel"); ?>
                    </a>
                </li>				
            </ul>
        </li>
        
        <!-- Third column -->
        <li class="col-sm-2">
            <ul>
                
                <!-- Settings -->
                <li class="dropdown-header">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/global.png" /> 
                    <?php eT('Settings');?>
                </li>
                
                <!-- Global settings -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/globalsettings"); ?>">
                        <?php eT("Global settings");?>
                    </a>
                </li>
                
                <!-- Edit label sets -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>">
                        <?php eT("Edit label sets");?>
                    </a>
                </li>
                
                <!-- Template Editor -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/templates/sa/view"); ?>">
                        <?php eT("Template Editor");?>
                    </a>
                </li>										
            </ul>
        </li>
        
        <!-- 4th column -->
        <li class="col-sm-2">
            <ul>
                
                <!-- Advanced -->
                <li class="dropdown-header">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/tools.png" />
                    <?php eT('Advanced');?>
                </li>
                
                <!-- Check Data Integrity -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/checkintegrity"); ?>">
                        <?php eT("Check Data Integrity");?>
                    </a>
                </li>
                
                <!-- Backup Entire Database -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/dumpdb"); ?>">
                        <?php eT("Backup Entire Database");?>
                    </a>
                </li>
                
                <!-- Plugin manager -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("/admin/pluginmanager/sa/index"); ?>">
                        <?php eT("Plugin manager");?>
                    </a>
                </li>           
            </ul>
        </li>
        
        <!-- 5th column -->
        <li class="col-sm-2 ">
            <ul>
                
                <!-- Expression Manager -->
                <li class="dropdown-header">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/expressionmanager_30.png" />
                    <?php eT("Expression Manager");?>
                </li>
                
                <!-- Expression Manager Descriptions -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl("admin/expressions"); ?>">
                        <?php eT("Expression Manager Descriptions");?>
                    </a>
                </li>
        
                <!--Available Functions -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>">
                        <?php eT("Available Functions");?>
                    </a>
                </li>
                
                <!--Unit Tests of Expressions Within Strings -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>">
                        <?php eT("Unit Tests of Expressions Within Strings");?>
                    </a>
                </li>
                
                <!-- Unit Test Dynamic Relevance Processing -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/relevance'); ?>">
                        <?php eT("Unit Test Dynamic Relevance Processing");?>
                    </a>
                </li>
                
                <!-- Preview Conversion of Conditions to Relevance -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/conditions2relevance'); ?>">
                        <?php eT("Preview Conversion of Conditions to Relevance");?>
                    </a>
                </li>
                
                <!-- Bulk Convert Conditions to Relevance -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/upgrade_conditions2relevance'); ?>">
                        <?php eT("Bulk Convert Conditions to Relevance");?>
                    </a>
                </li>
                
                <!-- Test Navigation -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/navigation_test'); ?>">
                        <?php eT("Test Navigation");?>
                    </a>
                </li>
                
                <!-- Show Survey logic file -->
                <li class="dropdown-item">
                    <a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_file'); ?>">
                        <?php eT("Show Survey logic file");?>
                    </a>
                </li>
            </ul>
        </li>
    </ul>				
</li>
