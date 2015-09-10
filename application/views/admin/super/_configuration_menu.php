			<!-- Configuration -->
			<li class="dropdown mega-dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/global.png" /> <?php eT('Configuration');?><span class="caret"></span></a>				
				<ul class="dropdown-menu mega-dropdown-menu">

                    <li class="col-sm-3 ">
                        <ul>
                            <div class="box">
                                <div class="box-icon">
                                    <span class="glyphicon glyphicon-info-sign" id="info-header"></span>
                                </div>
                                <div class="info">
                                    <h5 class="text-center"><?php eT("System overview"); ?></h5>
                                                    <dl class="dl-horizontal">
                                                        <dt class="text-info">Users</dt>
                                                        <dd>3</dd>
                                                        <dt class="text-info">Surveys</dt>
                                                        <dd>3</dd>
                                                        <dt class="text-info">Active surveys</dt>
                                                        <dd>1</dd>
                                                        <dt class="text-info">Deactivated result tables</dt>
                                                        <dd>1</dd>    
                                                        <dt class="text-info">Active token tables</dt>
                                                        <dd>0</dd>
                                                    </dl>
                                </div>
                            </div>
                            </ul>
                    </li>

				    
					<li class="col-sm-2">
						<ul>
							<li class="dropdown-header"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/user.png" /> <?php eT('Users');?></li>
							<li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/user/sa/index"); ?>"><?php eT("Manage survey administrators");?></a></li>
                            <li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/usergroups/sa/index"); ?>"><?php eT("Create/edit user groups");?></a></li>

				            	<li class="dropdown-item">
									<a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
										<?php eT("Central participant database/panel"); ?>
									</a>
				            	</li>				
						</ul>
					</li>
					<li class="col-sm-2">
						<ul>
							<li class="dropdown-header"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/global.png" /> <?php eT('Settings');?></li>
							<li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/globalsettings"); ?>"><?php eT("Global settings");?></a></li>
							<li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>"><?php eT("Edit label sets");?></a></li>
                            <li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/templates/sa/view"); ?>"><?php eT("Template Editor");?></a></li>										
						</ul>
					</li>
					<li class="col-sm-2">
						<ul>
							<li class="dropdown-header"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/tools.png" /> <?php eT('Advanced');?></li>
                            <li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/checkintegrity"); ?>"><?php eT("Check Data Integrity");?></a></li>
							<li class="dropdown-item"><a href="<?php echo $this->createUrl("admin/dumpdb"); ?>"><?php eT("Backup Entire Database");?></a></li>
							<li class="dropdown-item"><a href="<?php echo $this->createUrl("plugins/"); ?>"><?php eT("Plugin manager");?></a></li>           
						</ul>
					</li>

                    <li class="col-sm-2 ">
                        <ul>
                            <li class="dropdown-header"><img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/660B0B/expressionmanager_30.png" /> <?php eT("Expression Manager");?></li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl("admin/expressions"); ?>">
                                    <?php eT("Expression Manager Descriptions");?>
                                </a>
                            </li>

                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>"><?php eT("Available Functions");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>"><?php eT("Unit Tests of Expressions Within Strings");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/relevance'); ?>"><?php eT("Unit Test Dynamic Relevance Processing");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/conditions2relevance'); ?>"><?php eT("Preview Conversion of Conditions to Relevance");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/upgrade_conditions2relevance'); ?>"><?php eT("Bulk Convert Conditions to Relevance");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/navigation_test'); ?>"><?php eT("Test Navigation");?></a>
                            </li>
                            <li class="dropdown-item">
                                <a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_file'); ?>"><?php eT("Show Survey logic file");?></a>
                            </li>
                        </ul>
                    </li>

				</ul>				
			</li>