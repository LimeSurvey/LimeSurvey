<?php
   /**
    * This file render the sidemenu
    */
?>
    <!-- State when page is loaded : for JavaScript-->
    <?php if(isset($sidebar['state'])):?>
       <input type="hidden" id="close-side-bar" />
    <?php endif;?>

    <div class="absolute-wrapper hidden-xs" style="z-index: 100;"> </div>
    <!-- Menu -->
    <div class="side-menu  hidden-xs" id="sideMenu" style="z-index: 101;">

    <nav class="navbar navbar-default  hidden-xs" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header  hidden-xs">
        <div class="brand-wrapper  hidden-xs">
            <!-- Hamburger -->
            <button type="button" class="navbar-toggle hidden-xs">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Brand -->
            <div class="brand-name-wrapper  hidden-xs">
                <a class="navbar-brand hideside toggleside" href="#">
                    <?php eT('General');?>
                </a>
            </div>
            <a class="btn btn-default hide-button hidden-xs opened" id="chevronClose"  style="right: 30px">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a class="btn btn-default hide-button hidden-xs opened" id="chevronStretch">
                <span class="glyphicon glyphicon-chevron-right" ></span>
            </a>
        </div>

    </div>

    <!-- Main Menu -->
    <div class="side-menu-container  hidden-xs">
        <ul class="nav navbar-nav sidemenuscontainer  hidden-xs">

            <!-- Survey summary-->
            <li class="toWhite <?php if( isset($sidebar["survey_menu"]) ) echo 'active'; ?> ">
                <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    <?php eT("Survey");?>
                </a>
            </li>

            <!-- Question & Groups-->
            <li class="panel panel-default dropdownlvl1" id="dropdown">
                <a data-toggle="collapse" id="questions-groups-collapse" href="#dropdown-lvl1" <?php if( isset($sidebar["questiongroups"]) ) echo 'aria-expanded="true"'; ?>  >
                    <span class="glyphicon glyphicon-folder-open"></span> <?php eT('Questions and groups:');?>
                    <!-- <span class="glyphicon glyphicon-sort-by-order" id="sort-questions-button" aria-url="<?php echo $this->createUrl("admin/survey/sa/organize/surveyid/$surveyid"); ?>" ></span>-->
                   <span class="caret"></span>
                </a>

                <!-- Dropdown level 1 -->
                <div id="dropdown-lvl1" class="panel-collapse collapse <?php if( isset($sidebar["questiongroups"]) || isset($sidebar["listquestions"]) || 1==1 ) echo 'in'; ?>"  <?php if( isset($sidebar["questiongroups"]) || isset($sidebar["listquestions"]) ) echo 'aria-expanded="true"'; ?> >
                    <div class="panel-body">
                        <ul class="nav navbar-nav dropdown-first-level">

                            <!-- Explorer -->
                            <li class="panel panel-default" id="explorer" class="dropdownlvl2 dropdownstyle">
                                <a data-toggle="collapse" id="explorer-collapse" href="#explorer-lvl1">
                                    <span class="glyphicon glyphicon-folder-open"></span> <?php eT('Questions explorer');?>
                                   <span class="caret" ></span>
                                </a>

                                <div id="explorer-lvl1" class="panel-collapse collapse" >
                                    <div class="panel-body">
                                        <ul class="nav navbar-nav dropdown-first-level" id="explorer-container">
                                            <?php if(count($aGroups)):?>
                                                <?php foreach($aGroups as $aGroup):?>
                                                    <li class="panel panel-default dropdownstyle" id="">

                                                        <!-- Group Name -->
                                                        <a data-toggle="collapse" id="" href="#questiongroup-<?php echo $aGroup->gid; ?>" class="question-group-collapse">
                                                           <span class="question-group-collapse-title"><?php echo $aGroup->group_name;?><span class="caret"></span></span>
                                                        </a>

                                                        <div id="questiongroup-<?php echo $aGroup->gid; ?>" class="panel-collapse collapse questiongroupdropdown" >
                                                            <div class="panel-body">
                                                                <ul class="nav navbar-nav dropdown-first-level">

                                                                    <?php if(count($aGroup['aQuestions'])):?>
                                                                        <?php foreach($aGroup['aQuestions'] as $question):?>
                                                                        <!-- Question  -->
                                                                            <?php if($question->parent_qid == 0):?>

                                                                                <li class="toWhite">
                                                                                    <a href="<?php echo $this->createUrl("/admin/questions/sa/view/surveyid/$surveyid/gid/".$aGroup->gid."/qid/".$question->qid); ?>"">
                                                                                        <span class="question-collapse-title">
                                                                                            <span class="glyphicon glyphicon-list"></span>
                                                                                            <strong><?php echo sanitize_html_string($question->title);?> </strong>
                                                                                            <br/><em> <?php echo substr(sanitize_html_string($question->question), 0, 40);?></em>
                                                                                        </span>
                                                                                    </a>
                                                                                </li>
                                                                            <?php endif;?>
                                                                        <?php endforeach; ?>
                                                                    <?php else:?>
                                                                        <li class="toWhite">
                                                                            <a href="" onclick="event.preventDefault();" style="cursor: default;">
                                                                                <?php eT('no questions in this group');?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endif;?>

                                                                    <!-- add question to this group -->
                                                                    <li>
                                                                        <a class="text-success" href="<?php echo $this->createUrl("/admin/questions/sa/newquestion/surveyid/$surveyid/gid/$aGroup->gid"); ?>">
                                                                            <span class="glyphicon glyphicon-plus-sign"></span>
                                                                            <?php eT('Add new question to group');?>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach;?>
                                            <?php else:?>
                                            <li class="toWhite">
                                                <a href="" onclick="event.preventDefault();" style="cursor: default;">
                                                    <?php eT('No question group in this survey');?>
                                                </a>
                                            </li>
                                            <?php endif;?>
                                        </ul>
                                    </div>
                            </li>


                        <?php if($permission):?>
                            <!-- List Groups -->
                            <li class="toWhite <?php if( isset($sidebar["listquestiongroups"]) ) echo 'active'; ?>">
                                <!-- admin/survey/sa/view/surveyid/838454 listquestiongroups($iSurveyID)-->
                                <a href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/$surveyid"); ?>">
                                    <span class="glyphicon glyphicon-list"></span>
                                    <?php eT("List question groups");?>
                                </a>
                            </li>

                            <!-- List Questions -->
                            <li class="toWhite <?php if( isset($sidebar["listquestions"]) ) echo 'active'; ?>">
                                <a href="<?php echo $this->createUrl("admin/survey/sa/listquestions/surveyid/$surveyid"); ?>">
                                    <span class="glyphicon glyphicon-list"></span>
                                    <?php eT("List questions");?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Organize questions -->
                        <?php if($surveycontent):?>
                            <?php if ($activated):?>
                                <li class="disabled">
                                    <a href='#'>
                                        <span class="icon-organize"></span>
                                        <?php eT("Question group/question organizer disabled"); ?> - <?php eT("This survey is currently active."); ?>
                                     </a>
                                </li>
                                <?php else: ?>
                                <li>
                                    <a href="<?php echo $this->createUrl("admin/survey/sa/organize/surveyid/$surveyid"); ?>">
                                        <span class="icon-organize"></span>
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
