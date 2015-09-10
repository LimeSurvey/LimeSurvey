<?php
   /**
    * This file render the sidemenu
    */
?>


			<!-- State when page is loaded : for JavaScript-->
			<?php if(isset($sidebar['state'])):?>
				<input type="hidden" id="close-side-bar" />
			<?php endif;?>

    <!-- uncomment code for absolute positioning tweek see top comment in css -->
    <div class="absolute-wrapper"> </div> 
    <!-- Menu -->
    <div class="side-menu col-lg-4 offset-0">
    
    <nav class="navbar navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <div class="brand-wrapper">
            <!-- Hamburger -->
            <button type="button" class="navbar-toggle">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Brand -->
            <div class="brand-name-wrapper">
                <a class="navbar-brand hideside toggleside" href="#">
					<?php eT('General');?>
                </a>
            </div>
            <a class="btn btn-default hide-button hideside toggleside">
                <span class="glyphicon glyphicon-chevron-left" id="chevronside"></span>
            </a>
        </div>

    </div>

    <!-- Main Menu -->
    <div class="side-menu-container">
        <ul class="nav navbar-nav sidemenuscontainer">

			<!-- Survey summary-->
			<li class="toWhite <?php if( isset($sidebar["survey_menu"]) ) echo 'active'; ?> ">
				<a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>">
					<span class="glyphicon glyphicon-info-sign"></span>
					<?php eT("Survey");?>
				</a>
			</li>			

            <!-- Question & Groups-->
            <li class="panel panel-default" id="dropdown">
                <a data-toggle="collapse" id="questions-groups-collapse" href="#dropdown-lvl1" <?php if( isset($sidebar["questiongroups"]) ) echo 'aria-expanded="true"'; ?>  >
                    <span class="glyphicon glyphicon-folder-open"></span> <?php eT('Question and Groups:');?> 
                    <!-- <span class="glyphicon glyphicon-sort-by-order" id="sort-questions-button" aria-url="<?php echo $this->createUrl("admin/survey/sa/organize/surveyid/$surveyid"); ?>" ></span>-->
                   <span class="caret"></span>
                </a>
                <!-- Dropdown level 1 -->
                <div id="dropdown-lvl1" class="panel-collapse collapse <?php if( isset($sidebar["questiongroups"]) || isset($sidebar["listquestions"]) || 1==1 ) echo 'in'; ?>"  <?php if( isset($sidebar["questiongroups"]) || isset($sidebar["listquestions"]) ) echo 'aria-expanded="true"'; ?> >
                    <div class="panel-body">
                        <ul class="nav navbar-nav">

						<?php if(isset($sidebar['group_name'])):?>
							<li class="toWhite active">
								<a href="#">
									<?php eT("Question group :");?><br/>
									&nbsp; <?php echo $sidebar['group_name'];?>
								</a>
							</li>
						<?php endif;?>
                        	
			            <?php if($permission):?>
			            	<!-- Groups -->
			            	<li class="toWhite <?php if( isset($sidebar["listquestiongroups"]) ) echo 'active'; ?>">
			            		<!-- admin/survey/sa/view/surveyid/838454 listquestiongroups($iSurveyID)-->
				            	<a href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/$surveyid"); ?>">
				            		<span class="glyphicon glyphicon-list"></span>
				            		<?php eT("List question groups");?>
				            	</a>
			            	</li>
			            	
							<!-- Questions -->
							<li class="toWhite <?php if( isset($sidebar["listquestions"]) ) echo 'active'; ?>">
								<a href="<?php echo $this->createUrl("admin/survey/sa/listquestions/surveyid/$surveyid"); ?>">
									<span class="glyphicon glyphicon-list"></span>
									<?php eT("List questions");?>
								</a>
							</li>			            	
			            <?php endif; ?>			                                  	

			            <?php if($surveycontent):?>
			            <?php if ($activated):?>
			                    <li class="disabled">
			                    	<a href='#'>
			                    		<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/organize_disabled.png" title='' alt='<?php eT("Question group/question organizer disabled"); ?> - <?php eT("This survey is currently active."); ?>' />
			                    		<?php eT("Question group/question organizer disabled"); ?> - <?php eT("This survey is currently active."); ?>
			                         </a>
			                    </li>
			                    <?php else: ?>
			                    <li>
			                        <a href="<?php echo $this->createUrl("admin/survey/sa/organize/surveyid/$surveyid"); ?>">
			                            <img src='<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/organize.png' alt='<?php eT("Reorder question groups / questions"); ?>' "/>
			                            <?php eT("Reorder question groups / questions"); ?>
			                        </a>
			                    </li>
			                    <?php endif; ?>
			            <?php endif;?>  
                        	
                            
                        </ul>
                    </div>
                </div>
            </li>


			<!-- Token -->
            <?php if($tokenmanagement):?> 
            	<li class="toWhite  <?php if( isset($sidebar["token_menu"]) ) echo 'active'; ?> ">
	            	<a href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>">
	            		<span class="glyphicon glyphicon-user"></span>
	            		<?php eT("Token management");?>
	            	</a>
            	</li>
            <?php endif; ?>			            


			<!-- Survey List -->
            	<li class="toWhite" >
	            	<a href="<?php echo $this->createUrl("admin/survey/sa/listsurveys/"); ?>" class="" >
	            		<span class="glyphicon glyphicon-step-backward"></span>
	            		<?php eT("Return to survey list");?>
	            	</a>
            	</li>


        </ul>
    </div><!-- /.navbar-collapse -->
</nav>

 </div>
