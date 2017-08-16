<?php
/**
 * Survey default view
 * @var AdminController $this
 * @var Survey $oSurvey
 */
 $count= 0;

//TODO : move to controller
$templates = Template::getTemplateListWithPreviews();
//print_r($templates);
$count = 0;
$surveyid = $oSurvey->sid;

     $surveylocale = Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read');
     // EDIT SURVEY SETTINGS BUTTON
     $surveysettings = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read');
     $respstatsread = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')
         || Permission::model()->hasSurveyPermission($iSurveyID, 'statistics', 'read')
         || Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export');



?>
    <!-- Quick Actions -->
    <div id="survey-action-title" class="h3 pagetitle">
    <button data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/togglequickaction/");?>" id="survey-action-chevron" class="btn btn-default btn-tiny">
        <i class="fa fa-caret-down"></i>
    </button>&nbsp;&nbsp;
    <?php eT('Survey quick actions'); ?>
    </div>
        <div class="row welcome survey-action" id="survey-action-container" style="<?php if($quickactionstate==0){echo 'display:none';}?>">
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

                        <?php /* Commented out for the moment because it is not properly working
                        if(intval($templateapiversion) < intval(App()->getConfig("versionnumber")) ):?>
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                                <strong><?php eT('This template is out of date.');?></strong> <?php eT('We can not guarantee optimum operation. It would be preferable to use a new template.');?>
                            </div>
                        <?php endif; */ ?>
                    </div>
                </div>

                <!-- Boxes and template -->
                <div class="row">

                    <!-- Boxes -->
                    <div class="col-sm-6">

                        <!-- Switch : Show questions group by group -->
                        <?php $switchvalue = ($oSurvey->format=='G') ? 1 : 0 ; ?>
                        <?php if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
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
                        <?php if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                            <!-- Template carroussel -->
                            <?php $this->renderPartial( "/admin/survey/subview/_template_carousel", array(
                                'templates'=>$templates,
                                'oSurvey'=>$oSurvey,
                                'iSurveyId'=>$surveyid,
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

    <div class="row">
        <!-- Survey summary -->
        <div class="col-sm-12 h3 pagetitle"><?php eT('Survey summary'); ?></div>

        <div class="col-sm-12 h4"><?php echo flattenText($oSurvey->currentLanguageSettings->surveyls_title)." (".gT("ID")." ".$oSurvey->sid.")";?></div>
        <div class="col-md-12 col-lg-6">
            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">
                    <strong> <?php neT("Survey URL:|Survey URLs:",count($aAdditionalLanguages)+1);?></strong>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <!-- Base language -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php echo getLanguageNameFromCode($oSurvey->language,false); ?>  <?php eT('(Base language)');?>:
                            </div>
                            <div class="col-8">
                                <?php $tmp_url = $this->createAbsoluteUrl("survey/index",array("sid"=>$oSurvey->sid,"lang"=>$oSurvey->language)); ?>
                                <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
                            </div>
                        </div>
                    </li>
                    <!-- Additional languages  -->
                    <?php foreach ($aAdditionalLanguages as $langname): ?>
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php echo getLanguageNameFromCode($langname,false).":";?>
                            </div>
                            <div class="col-8">
                                <?php $tmp_url = $this->createAbsoluteUrl("/survey/index",array("sid"=>$oSurvey->sid,"lang"=>$langname)); ?>
                                <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <!-- End URL -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("End URL:");?>
                            </div>
                            <div class="col-8">
                                    <?php echo $endurl;?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-12 col-lg-6">
            <!-- Survey's texts -->
            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">
                    <strong><?php eT("Survey texts");?>:</strong>
                </div>
                <!-- List group -->
                <ul class="list-group">
                        <!-- Description -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("Description:");?>
                            </div>
                            <div class="col-8">
                                <?php
                                    if (trim($oSurvey->currentLanguageSettings->surveyls_description) != '')
                                    {
                                        templatereplace(flattenText($oSurvey->currentLanguageSettings->surveyls_description));
                                        echo LimeExpressionManager::GetLastPrettyPrintExpression();
                                    }
                                ?>
                            </div>
                        </div>
                    </li>
                    <!-- Welcome -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("Welcome:");?>
                            </div>
                            <div class="col-8">
                                <?php
                                    templatereplace(flattenText($oSurvey->currentLanguageSettings->surveyls_welcometext));
                                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                                ?>
                            </div>
                        </div>
                    </li>
                    
                    <!-- End message -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("End message:");?>
                            </div>
                            <div class="col-8">
                                    <?php
                                    templatereplace(flattenText($oSurvey->currentLanguageSettings->surveyls_endtext));
                                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                                ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">    
        <div class="col-md-12 col-lg-6">
            <ul class="list-group">
                    <!-- Administrator -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Administrator:");?></strong>
                        </div>
                        <div class="col-8">
                                <?php echo flattenText("{$oSurvey->admin} ({$oSurvey->adminemail})");?>
                        </div>
                    </div>
                </li>
                <!-- Fax to -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Fax to:");?></strong>
                        </div>
                        <div class="col-8">
                            <?php echo flattenText($oSurvey->faxto);?>
                        </div>
                    </div>
                </li>
                
                <!-- Number of questions/groups -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Number of questions/groups:");?></strong>
                        </div>
                        <div class="col-8">
                            <?php echo $sumcount3."/".$sumcount2;?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-md-12 col-lg-6">
            <ul class="list-group">
                <!-- Start date/time -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Start date/time:");?></strong>
                        </div>
                        <div class="col-8">
                            <?php echo $startdate;?>
                        </div>
                    </div>
                </li>
                <!-- Expiration date/time -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Expiration date/time:");?></strong>
                        </div>
                        <div class="col-8">
                            <?php echo $expdate;?>
                        </div>
                    </div>
                </li>
                
                <!-- Template -->
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Template:");?></strong>
                        </div>
                        <div class="col-8">
                            <?php $templatename = $oSurvey->template;
                            if (Permission::model()->hasGlobalPermission('templates','read'))
                            {
                                $sTemplateOptionsUrl = $this->createUrl("admin/templateoptions/sa/updatesurvey",array('surveyid'=>$oSurvey->sid, "gsid"=>$oSurvey->gsid)); 
                                $sTemplateEditorUrl = $this->createUrl("admin/templates/sa/view",array('templatename' => $oSurvey->template)); 
                                //$sTemplateEditorUrl = $this->createUrl("admin/templates/sa/view",array('editfile'=>'layout_first_page.twig', "screenname"=>'welcome', 'template' => $oSurvey->template)); 
                                ?>
                                <?php echo $templatename; ?>
                                <a href='<?=$sTemplateOptionsUrl?>' title="<?php eT("Open template options"); ?>" class="btn btn-default btn-xs"><i class="fa fa-paint-brush"></i></a>
                                <a href='<?=$sTemplateEditorUrl?>' title="<?php eT("Open template editor in new window"); ?>" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-object-group"></i></a>
                                <?php
                            }
                            else
                            {
                                echo $templatename;
                            }
                            ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-6">
            <!-- Hints  -->
            <div class="ls-flex-row col-12">
                <div class="col-4">
                    <strong><?php eT("Survey settings:");?></strong>
                </div>
                <div class="col-8">
                    <?php echo $warnings.$hints;?>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6">
        <!-- usage -->
        <?php if ($tableusage != false){
                if ($tableusage['dbtype']=='mysql' || $tableusage['dbtype']=='mysqli'){
                    $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                    $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2); ?>
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                                <strong><?php eT("Table column usage");?>: </strong>
                        </div>
                        <div class="col-8">
                                <div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> 
                        </div>
                    </div>
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Table size usage");?>: </strong>
                        </div>
                        <div class="col-8">
                            <div class='progressbar' style='width:20%; height:15px;' name='<?php echo $size_usage;?>'></div>
                        </div>
                    </div>
                <?php }
                elseif (($arrCols['dbtype'] == 'mssql')||($arrCols['dbtype'] == 'postgre')||($arrCols['dbtype'] == 'dblib')){
                    $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2); ?>
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <strong><?php eT("Table column usage");?>: </strong>
                        </div>
                        <div class="col-8">
                            <strong><?php echo $column_usage;?>%</strong>
                            <div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> 
                        </div>
                    </div>
                <?php }
            } ?>
            </div>      
        </div>
    </div>
</div>
