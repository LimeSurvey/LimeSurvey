<?php
/**
 * Survey default view
 * @var AdminController $this
 * @var Survey $oSurvey
 */
 $count= 0;
 $templates = Template::getTemplateListWithPreviews();
 $surveylocale = Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveylocale', 'read');
 // EDIT SURVEY SETTINGS BUTTON
 $surveysettings = Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveysettings', 'read');
 $respstatsread = Permission::model()->hasSurveyPermission($oSurvey->sid, 'responses', 'read')
     || Permission::model()->hasSurveyPermission($oSurvey->sid, 'statistics', 'read')
     || Permission::model()->hasSurveyPermission($oSurvey->sid, 'responses', 'export');
 $groups_count = count($oSurvey->groups);
 
?>

<!-- Quick Actions -->

<div class="panel panel-default">
    <div id="survey-action-title" class="panel-heading" >
        <div class="row">
            <div class="col-xs-2 col-sm-1">
                <button id="survey-action-chevron" class="btn btn-default btn-tiny" data-active="<?=$quickactionstate?>" data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/togglequickaction/");?>">
                    <i class="fa <?=($quickactionstate > 0 ?  'fa-caret-up' : 'fa-caret-down')?>"></i>
                </button>
            </div>
            <div class="col-xs-10 col-sm-11 h4">
                <?php eT('Survey quick actions'); ?>
            </div>
        </div>
    </div>
    <div class="panel-body" style="display:<?=($quickactionstate > 0 ? 'block' : 'none')?>" id="survey-action-container"> 
        <div class="row welcome survey-action">
            <div class="col-sm-12 content-right">
                <!-- Alerts, infos... -->
                <div class="row">
                    <div class="col-sm-12">
                        <!-- While survey is activated, you can't add or remove group or question -->
                        <?php if ($oSurvey->isActive): ?>
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                                <strong><?php eT('Warning!');?></strong> <?php eT("While the survey is activated, you can't add or remove a group or question.");?>
                            </div>

                        <?php elseif(!$groups_count > 0):?>

                            <!-- To add questions, first, you must add a question group -->
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                                <strong><?php eT('Warning!');?></strong> <?php eT('Before you can add questions you must add a question group first.');?>
                            </div>

                            <!-- If you want a single page survey, just add a single group, and switch on "Show questions group by group -->
                            <div class="alert alert-info alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                                <span class="fa fa-info-sign" ></span>&nbsp;&nbsp;&nbsp;
                                <?php eT('Set below if your questions are shown one at a time, group by group or all on one page.');?>
                            </div>
                        <?php endif;?>
                    </div>
                </div>

                <!-- Boxes and template -->
                <div class="row">

                    <!-- Boxes -->
                    <div class="col-sm-6">

                        <!-- Switch : Show questions group by group -->
                        <?php $switchvalue = ($oSurvey->format=='G') ? 1 : 0 ; ?>
                        <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid,'surveycontent','update')): ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="switch"><?php eT('Format:');?></label>
                                    <div id='switchchangeformat' class="btn-group" role="group">
                                        <button id='switch' type="button" data-value='S' class="btn btn-default <?php if($oSurvey->format=='S'){echo 'active';}?>"><?php eT('Question by question');?></button>
                                        <button type="button" data-value='G' class="btn btn-default <?php if($oSurvey->format=='G'){echo 'active';}?>"><?php eT('Group by group');?></button>
                                        <button type="button" data-value='A' class="btn btn-default <?php if($oSurvey->format=='A'){echo 'active';}?>"><?php eT('All in one');?></button>
                                    </div>
                                    <input type="hidden" id="switch-url" data-url="<?php echo $this->createUrl("admin/survey/sa/changeFormat/surveyid/".$oSurvey->sid);?>" />
                                    <br/><br/>

                                </div>
                            </div>
                        <?php endif; ?>


                        <!-- Add Question / group -->
                        <div class="row row-eq-height">
                            <!-- Survey active, so it's impossible to add new group/question -->
                            <?php if ($oSurvey->isActive): ?>

                                    <!-- Can't add new group to survey  -->
                                    <div class="col-sm-6">
                                        <div class="panel panel-primary disabled" id="panel-1">
                                            <div class="panel-heading">
                                                <div class="panel-title h4"><?php eT('Add group');?></div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="panel-body-ico">
                                                    <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip">
                                                        <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                                        <span class="sr-only"><?php eT('Add new group');?></span>
                                                    </a>
                                                </div>
                                                <div  class="panel-body-link">
                                                    <p><a href="#"><?php eT('Add new group');?></a></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Can't add a new question -->
                                    <div class="col-sm-6" >
                                        <div class="panel panel-primary disabled" id="panel-2">
                                            <div class="panel-heading">
                                                <div class="panel-title h4 disabled"><?php eT('Add question');?></div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="panel-body-ico">
                                                    <a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip">
                                                        <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                                        <span class="sr-only"><?php eT('Add new question');?></span>
                                                    </a>
                                                </div>
                                                <div  class="panel-body-link">
                                                    <p>
                                                        <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                                                            <?php eT("Add new question"); ?>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- survey is not active, and user has permissions, so buttons are shown and active -->
                                <?php elseif(Permission::model()->hasSurveyPermission($oSurvey->sid,'surveycontent','create')): ?>

                                    <!-- Add group -->
                                    <div class="col-sm-6">
                                        <div class="panel panel-primary panel-clickable" id="panel-1" data-url="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$oSurvey->sid); ?>">
                                            <div class="panel-heading">
                                                <div class="panel-title h4"><?php eT('Add group');?></div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="panel-body-ico">
                                                    <a  href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$oSurvey->sid); ?>" >
                                                        <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                                        <span class="sr-only"><?php eT('Add new group');?></span>
                                                    </a>
                                                </div>
                                                <div  class="panel-body-link">
                                                    <p><a href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$oSurvey->sid); ?>"><?php eT('Add new group');?></a></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Survey has no group, so can't add a question -->
                                    <?php if(!$groups_count > 0): ?>
                                        <div class="col-sm-6" >
                                            <div class="panel panel-primary disabled" id="panel-2">
                                                <div class="panel-heading">
                                                    <div class="panel-title h4 disabled"><?php eT('Add question');?></div>
                                                </div>
                                                <div class="panel-body  ">
                                                    <div class="panel-body-ico">
                                                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                                                            <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                <span class="sr-only"><?php eT("You must first create a question group."); ?></span>
                                                        </a>
                                                    </div>
                                                    <div  class="panel-body-link">
                                                        <p>
                                                            <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                                                                <?php eT("Add new question"); ?>
                                <span class="sr-only"><?php eT("Add new question"); ?></span>
                                                            </a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Survey has a group, so can add a question -->
                                    <?php else:?>
                                        <div class="col-sm-6">
                                            <div class="panel panel-primary panel-clickable" id="panel-2" data-url="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$oSurvey->sid); ?>">
                                                <div class="panel-heading">
                                                    <div class="panel-title h4"><?php eT('Add question');?></div>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="panel-body-ico">
                                                        <a  href="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$oSurvey->sid); ?>" >
                                                            <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                <span class="sr-only"><?php eT('Add question');?></span>
                                                        </a>
                                                    </div>
                                                    <div  class="panel-body-link">
                                                        <p><a href="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$oSurvey->sid); ?>"><?php eT("Add new question"); ?></a></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                        </div>

                        <div class="row row-eq-height">
                            <div class="col-sm-6">


                                <!-- Edit text elements and general settings -->
                                <?php if($surveylocale && $surveysettings): ?>
                                    <div class="panel panel-primary panel-clickable" id="panel-3" data-url="<?php echo $this->createUrl("admin/survey/sa/editlocalsettings/surveyid/".$oSurvey->sid); ?>">
                                        <div class="panel-heading">
                                            <div class="panel-title h4"><?php eT('Edit text elements and general settings');?></div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="panel-body-ico">
                                                <a  href="<?php echo $this->createUrl("admin/survey/sa/editlocalsettings/surveyid/".$oSurvey->sid); ?>" >
                                                    <span class="icon-edit text-success"  style="font-size: 3em;"></span>
                            <span class="sr-only"><?php eT('Edit text elements and general settings');?></span>
                                                </a>
                                            </div>
                                            <div  class="panel-body-link">
                                                <p><a href="<?php echo $this->createUrl("admin/survey/sa/editlocalsettings/surveyid/".$oSurvey->sid); ?>"><?php eT('Edit text elements and general settings');?></a></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="panel panel-primary disabled" id="panel-3" >
                                        <div class="panel-heading">
                                            <div class="panel-title h4"><?php eT('Edit text elements and general settings');?></div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="panel-body-ico">
                                                <a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("We are sorry but you don't have permissions to do this."); ?>" style="display: inline-block" data-toggle="tooltip" >
                                                    <span class="icon-edit text-success"  style="font-size: 3em;"></span>
                            <span class="sr-only"><?php eT('Edit text elements and general settings');?></span>
                                                </a>
                                            </div>
                                            <div  class="panel-body-link">
                                                <p><a href="#"><?php eT('Edit text elements and general settings');?></a></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;?>
                            </div>


                            <!-- Stats -->
                            <?php if($respstatsread && $activated=="Y"):?>
                                <div class="col-sm-6">
                                    <div class="panel panel-primary panel-clickable" id="panel-4" data-url="<?php echo $this->createUrl("admin/statistics/sa/simpleStatistics/surveyid/".$oSurvey->sid); ?>">
                                        <div class="panel-heading">
                                            <div class="panel-title h4"><?php eT("Statistics");?></div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="panel-body-ico">
                                                <a  href="<?php echo $this->createUrl("admin/statistics/sa/simpleStatistics/surveyid/".$oSurvey->sid); ?>" >
                                                    <span class="fa fa-bar-chart text-success"  style="font-size: 3em;"></span>
                            <span class="sr-only"><?php eT("Statistics");?></span>
                                                </a>
                                            </div>
                                            <div  class="panel-body-link">
                                                <p>
                                                    <a href="<?php echo $this->createUrl("admin/statistics/sa/simpleStatistics/surveyid/".$oSurvey->sid); ?>">
                                                        <?php eT("Responses & statistics");?>
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-sm-6">
                                    <div class="panel panel-primary disabled" id="panel-4">
                                        <div class="panel-heading">
                                            <div class="panel-title h4"><?php eT("Responses & statistics");?></div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="panel-body-ico">
                                                <a  href="#" >
                                                    <span class="fa fa-bar-chart text-success"  style="font-size: 3em;"></span>
                            <span class="sr-only"><?php eT("Responses & statistics");?></span>
                                                </a>
                                            </div>
                                            <div  class="panel-body-link">
                                                <p>
                                                    <a href="#" title="<?php if($activated!="Y"){eT("This survey is not active - no responses are available.");}else{eT("We are sorry but you don't have permissions to do this.");} ?>" style="display: inline-block" >
                                                        <?php eT("Responses & statistics");?>
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;?>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid,'surveycontent','update')): ?>
                            <!-- Template carroussel -->
                            <?php $this->renderPartial( "/admin/survey/subview/_template_carousel", array(
                                'templates'=>$templates,
                                'oSurvey'=>$oSurvey,
                                'iSurveyId'=>$oSurvey->sid,
                            )); ?>
                        <?php endif; ?>
                    </div>

                    <!-- last visited question -->
                    <?php if($showLastQuestion):?>
                        <div class="row text-left">
                            <div class="col-sm-12">
                                <?php eT("Last visited question:");?>
                                <a href="<?php echo $last_question_link;?>" class=""><?php echo viewHelper::flatEllipsizeText($last_question_name, true, 60); ?></a>
                                <br/><br/>
                            </div>
                        </div>
                    <?php endif;?>

                </div> <!-- row boxes and template-->
            </div>
        </div>
    </div>
</div>
<?php 
    Yii::app()->getClientScript()->registerScript('Quickaction-activate', 
        "$('#survey-action-chevron').off('click').on('click', surveyQuickActionTrigger);", 
        LSYii_ClientScript::POS_POSTSCRIPT
    ); 
?>
