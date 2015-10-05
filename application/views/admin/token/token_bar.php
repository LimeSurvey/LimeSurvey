<?php
App()->getClientScript()->registerPackage('jqueryui-timepicker');
?>
<div class='menubar surveybar' id="tokenbarid">
    <div class='row container-fluid'>
        
        <!-- left buttons -->
        <div class="col-md-9">
            
            <!-- Token view buttons -->
            <?php if( isset($token_bar['buttons']['view']) ): ?>

                <!-- Display tokens -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'read')): ?>
                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>' role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/document.png" />
                        <?php eT("Display tokens"); ?>
                    </a>
                <?php endif; ?>

                <!-- Create tokens -->
                <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png" /> 
                    <?php eT("Create tokens");?> <span class="caret"></span>
                </button>
                
                <!-- Add new token entry -->
                <ul class="dropdown-menu">
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'create')): ?>
                <li>
                    <a href="<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>" >
                        <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png' />
                        <?php eT("Add new token entry"); ?>
                    </a>
                </li>
                
                <!-- Create dummy tokens -->
                <li>
                    <a href="<?php echo $this->createUrl("admin/tokens/sa/adddummies/surveyid/$surveyid"); ?>" >
                       <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/create_dummy_token.png' />
                       <?php eT("Create dummy tokens"); ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Import tokens -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'import')): ?>
                    <li role="separator" class="divider"></li>
                    <small><?php eT("Import tokens"); ?> : </small>
                    
                    <!-- from CSV file -->
                    <li>
                       <a href="<?php echo $this->createUrl("admin/tokens/sa/import/surveyid/$surveyid") ?>" >
                           <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/importcsv.png" />
                           <?php eT("from CSV file"); ?>
                       </a>
                    </li>
                    
                    <!-- from LDAP query -->
                    <li>
                        <a href="<?php echo $this->createUrl("admin/tokens/sa/importldap/surveyid/$surveyid") ?>" >
                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/importldap.png" />
                            <?php eT("from LDAP query"); ?>
                        </a>
                    </li>
                <?php endif; ?>
                </ul>
                </div>

                <!-- Manage additional attribute fields -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update') || Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')): ?>
                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$surveyid"); ?>' role="button">
                       <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/token_manage.png" />
                       <?php eT("Manage additional attribute fields"); ?>
                    </a>
                <?php endif; ?>            

                <!-- Export tokens to CSV file -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'export')): ?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$surveyid"); ?>" role="button">
                       <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/exportcsv.png" />
                       <?php eT("Export tokens to CSV file"); ?>
                    </a>
                <?php endif; ?>
                
                <!-- EMAILS -->                
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')):?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/emailtemplates.png" /> 
                        <?php eT("Tokens email");?> <span class="caret"></span>
                    </button>
                    
                    <ul class="dropdown-menu">
                        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'create')): ?>
                            
                        <!-- Send email invitation -->
                        <li>
                            <a href="<?php echo $this->createUrl("admin/tokens/sa/email/surveyid/$surveyid"); ?>" >
                                <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/invite.png' />
                                <?php eT("Send email invitation"); ?>
                            </a>
                        </li>					
                        
                        <!-- Send email reminder -->
                        <li>
                            <a href="<?php echo $this->createUrl("admin/tokens/sa/email/action/remind/surveyid/$surveyid"); ?>" >
                                <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/remind.png' />
                                <?php eT("Send email reminder"); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li role="separator" class="divider"></li>
                        
                        <!-- Bounce settings -->
                        <li>
                            <a href="<?php echo $this->createUrl("admin/tokens/sa/bouncesettings/surveyid/$surveyid"); ?>" >
                                <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/bounce_settings.png' />
                                <?php eT("Bounce settings"); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Generate tokens -->                
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/tokens/sa/tokenify/surveyid/$surveyid"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/tokenify.png" />
                    <?php eT("Generate tokens"); ?>
                </a>
                <?php endif; ?>
            <?php endif;?>
        </div>
        
        <!-- Right buttons -->
        <div class="col-md-3 text-right">
            
            <!-- View token buttons -->
            <?php if( isset($token_bar['buttons']['view'] )): ?>
                
                <!-- Delete tokens table -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','delete')): ?>
                    <a class="btn btn-danger" href="<?php echo $this->createUrl("admin/tokens/sa/kill/surveyid/$surveyid"); ?>" role="button">
                        <?php eT("Delete tokens table"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Save buttons -->            
            <?php if(isset($token_bar['savebutton']['form'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-button" data-use-form-id="<?php if (isset($token_bar['savebutton']['useformid'])){ echo '1';}?>" data-form-to-save="<?php if (is_string($token_bar['savebutton']['form'])) {echo $token_bar['savebutton']['form']; }?>">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php eT("Save");?>
                </a>
                
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/282267{$surveyid}"); ?>" role="button">
                    <span class="glyphicon glyphicon-saved" aria-hidden="true"></span>
                    <?php eT("Save and close");?>
                </a>
            <?php endif;?>
            
            <!-- Close -->
            <?php if(isset($token_bar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $this->createUrl($token_bar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
            
            <!-- Return -->
            <?php if(isset($token_bar['returnbutton'])):?>
                <a class="btn btn-default" href="<?php echo $token_bar['returnbutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
                    <?php echo $token_bar['returnbutton']['text'];?>
                </a>
            <?php endif;?>
        </div>
    	
    </div>
</div>