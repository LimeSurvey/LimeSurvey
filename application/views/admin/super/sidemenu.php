<?php
   /**
    * This view displays the sidemenu on the left side, containing the question explorer
    *
    * Var to manage open/close state of the sidemenu, question explorer :
    * @var $sidemenu['state'] : if set, the sidemnu is close
    * @var $sidemenu['explorer']['state'] : if set to true, question explorer will be opened
    */
?>
<?php
    $sidemenu['state'] = isset($sidemenu['state']) ? $sidemenu['state'] : true;
    if ($sideMenuBehaviour == 'alwaysClosed'
        || ($sideMenuBehaviour == 'adaptive'
        && !$sidemenu['state']))
    {
        $showSideMenu = false;
    }
    else
    {
        $showSideMenu = true;
    }
?>
<?php
    // TODO : move to controller
    $bSurveyIsActive = (isset($surveyIsActive))?$surveyIsActive:$oSurvey->active=='Y';
    $sidemenu = (isset($sidemenu))?$sidemenu:array();
?>

    <!-- State when page is loaded : for JavaScript-->
    <?php if ($sideMenuBehaviour == 'adaptive' || $sideMenuBehaviour == ''): ?>
        <?php if(isset($sidemenu['state']) && $sidemenu['state']==false ):?>
           <input type="hidden" id="close-side-bar" />
        <?php endif;?>
    <?php elseif ($sideMenuBehaviour == 'alwaysClosed'): ?>
           <input type="hidden" id="close-side-bar" />
    <?php elseif ($sideMenuBehaviour == 'alwaysOpen'): ?>
        <!-- Do nothing -->
    <?php endif;?>

<script>
var generalInfoTitle = "<?php eT('Show information abaout this Question/Questiongroup'); ?>";
</script>

<div id="sideMenuContainer" class="sidemenu-container">
    <!-- sideMenu -->
    <div class="side-menu hidden-xs" id="sideMenu" style="z-index: 101;">
        <nav class="navbar navbar-default hidden-xs">

            <!-- Header : General -->
            <div class="navbar-header  hidden-xs">
                <div class="brand-wrapper  hidden-xs">

                    <!-- Hamburger for futur mobile design-->
                    <button type="button" class="navbar-toggle hidden-xs">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <div class='row no-gutter'>

                        <!-- Brand -->
                        <a id='sidemenu-home' class="col-sm-7 navbar-brand hideside toggleside col-xs-12" href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>">
                            <div class="brand-name-wrapper hidden-xs">
                                    <span class="fa fa-home"></span>&nbsp;
                                    <?php eT("Survey");?>
                            </div>
                        </a>

                        <!-- chevrons to stretch the side menu -->
                        <?php if (getLanguageRTL($_SESSION['adminlang'])): ?>
                            <div class='col-sm-5 col-xs-12'>
                                <a style="z-index:1000001" class="btn btn-default btn-disabled hide-button hidden-xs opened pull-right" title="<?php eT('Drag to resize'); ?>" data-toggle="tooltip" id="scaleSidebar">
                                    <i class="fa fa-bars" style="transform:rotate(90deg);">&nbsp;</i>
                                </a>
                                <a class="btn btn-default hide-button hidden-xs opened pull-right" data-collapsed="<?php echo !$showSideMenu; ?>" id="chevronClose">
                                    <i class="fa fa-chevron-right"></i>
                                </a>

                            </div>
                        <?php else: ?>
                            <div class='col-sm-5 col-xs-12'>
                                <a style="z-index:1000001" class="btn btn-default btn-disabled hide-button hidden-xs opened pull-right" title="<?php eT('Drag to resize'); ?>" data-toggle="tooltip" id="scaleSidebar">
                                    <i class="fa fa-bars" style="transform:rotate(90deg);">&nbsp;</i>
                                </a>
                                <a class="btn btn-default hide-button hidden-xs opened pull-right" data-collapsed="<?php echo !$showSideMenu; ?>" id="chevronClose">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>



            <?php 
            echo $quickmenu; 
            /*var_dump($oSurvey);*/
            $activeQuestion = Yii::app()->request->getQuery('qid', null); 
            $activeQuestionGroup = Yii::app()->request->getQuery('gid', null); 
            $newQuestionGroupLink = $this->createUrl("admin/questiongroups/sa/add/surveyid/".$surveyid);
            $newQuestionToGroupLink = $this->createUrl("admin/questions/sa/newquestion/surveyid/".$surveyid."/gid/".$activeQuestionGroup);
            ?>
            <!-- Main Menu -->
            <div class="side-menu-container hidden-xs">
            <!-- Add new Questiongroup, add new Question => quickadd -->
                <div class="container-fluid" id="quickadd-button-bar">
                    <div class="row">
                        <?php if($activeQuestionGroup): ?>
                        <div class="btn-group col-xs-8" role="group">
    <button id="quickadd-add-new-questiongroup" onclick="location.href='<?php echo $newQuestionGroupLink; ?>'" title="<?php eT('Add questiongroup to current survey');?>" data-toggle="tooltip" class="btn btn-default"><i class="icon-add"></i>&nbsp;<?php eT("Group");?></button>
    <button id="quickadd-add-new-question" onclick="location.href='<?php echo $newQuestionToGroupLink; ?>'" title="<?php eT('Add question to current questiongroup');?>" data-toggle="tooltip" class="btn btn-primary"><i class="icon-add"></i>&nbsp;<?php eT("Question");?></button>
                        <?php else: ?>
                        <div class="btn-group col-xs-8" role="group">
    <button id="quickadd-add-new-questiongroup" onclick="location.href='<?php echo $newQuestionGroupLink; ?>'" title="<?php eT('Add questiongroup to current survey');?>" data-toggle="tooltip" class="btn btn-default btn-block"><i class="icon-add"></i>&nbsp;<?php eT("Group");?></button>
                        <?php endif; ?>
                        </div>
                        <div class="col-xs-4">
                            <button id="fancytree_expand_all_nodes" class="btn btn-link btn-lg col-xs-6" data-toggle="tooltip" title="<?php eT('Expand all questionsgroups');?>"><i class="fa fa-expand">&nbsp;</i></button>
                            <button id="fancytree_compress_all_nodes" class="btn btn-link btn-lg col-xs-6" data-toggle="tooltip" title="<?php eT('Compress all questionsgroups');?>"><i class="fa fa-compress">&nbsp;</i></button>
                        </div>
                    </div>
                </div>
                <ul class="nav navbar-nav sidemenuscontainer hidden-xs" style="">
                    <!-- Question & Groups-->
                    <li class="panel panel-default dropdownlvl1" id="dropdown">
                        <ul class="nav navbar-nav dropdown-first-level">
                             
                        <!-- Question Explorer -->
                    <div id="dropdown-lvl1" >
                        <div class="panel-body">
                                    <!-- Explorer -->
                                    <?php $this->renderPartial( "/admin/super/_question_explorer", array(
                                        'sidemenu' => $sidemenu,
                                        'aGroups' => $aGroups,
                                        'iSurveyId' => $surveyid,
                                        'bSurveyIsActive' => $bSurveyIsActive,
                                        'language' => $oSurvey->language,
                                        'iQuestionId' => $activeQuestion,
                                        'iQuestionGroupId' => $activeQuestionGroup,
                                    )); ?>
                            </div>
                        </div>
                        </ul>
                    </li>

                    <!-- Token -->
                    <?php if($tokenmanagement):?>
                        <li id="tokensidemenu" class="toWhite  <?php if( isset($sidemenu["token_menu"]) ) echo 'active'; ?> ">
                            <a href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>">
                                <span class="glyphicon glyphicon-user"></span>
                                <?php eT("Survey participants");?>
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
</div>
