<?php
/**
 * Survey default view
 * @var AdminController $this
 * @var Survey $oSurvey
 */
 $count= 0;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveySummary');

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
<!-- START surveySummary -->
<div class="row">
    <div class="col-sm-12 h3 pagetitle">
        <?php eT('Survey summary'); ?> :
        <?php echo flattenText($oSurvey->currentLanguageSettings->surveyls_title)." (".gT("ID")." ".$oSurvey->sid.")";?>
    </div>
</div>
<?php /*
/// Survey quick actions have been removed -> deprecated
<div class="row">
    <div class="col-sm-12">
        <?php echo $this->renderPartial('/admin/survey/subview/_survey_quickaction', $subviewData); ?>    
    </div>
</div>
*/ ?>
<div class="row ls-space margin top-10">
        <!-- Survey summary -->
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <strong> <?php neT("Survey URL:|Survey URLs:",count($aAdditionalLanguages)+1);?></strong>
            </div>
            <!-- List group -->
            <ul class="list-group">
                <!-- Base language -->
                <li class="list-group-item" id="adminpanel__surveysummary--mainLanguageLink">
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
                <?php foreach ($aAdditionalLanguages as $langname)
                {
                    ?>
                <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <?php echo getLanguageNameFromCode($langname, false).":"; ?>
                        </div>
                        <div class="col-8">
                            <?php $tmp_url = $this->createAbsoluteUrl("/survey/index", array("sid"=>$oSurvey->sid,"lang"=>$langname)); ?>
                            <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
                        </div>
                    </div>
                </li>
                <?php
                } ?>
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
                    <!-- Number of questions/groups -->
                    <li class="list-group-item">
                    <div class="ls-flex-row col-12">
                        <div class="col-4">
                            <?php eT("Number of questions/groups:");?>
                        </div>
                        <div class="col-8">
                            <?php echo $sumcount3."/".$sumcount2;?>
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
                <strong><?php eT("Text elements");?>:</strong>
                <a class="pull-right btn btn-default btn-xs pjax" data-toggle="tooltip" title="<?=gT('Survey text elements')?>" href="<?=$this->createUrl('admin/survey/sa/rendersidemenulink', ['subaction'=>'surveytexts', 'surveyid'=>$surveyid])?>">
                    <i class="fa fa-cogs"></i>
                </a>
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
                                $fullWelcomeText = LimeExpressionManager::GetLastPrettyPrintExpression();

                                $this->widget('ext.admin.TextDisplaySwitch.TextDisplaySwitch', array(
                                        'widgetsJsName' => "welcome_text",
                                        'textToDisplay' => $fullWelcomeText
                                    ));
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
                                $fullSurveyEndText = LimeExpressionManager::GetLastPrettyPrintExpression();
                                $this->widget('ext.admin.TextDisplaySwitch.TextDisplaySwitch', array(
                                    'widgetsJsName' => "end_text",
                                    'textToDisplay' => $fullSurveyEndText
                                ));
                        ?>
                        </div>
                    </div>
                </li>
                <?php if($oSurvey->showsurveypolicynotice > 0) { ?>
                    <!-- Data security notice -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("Data security notice:");?>
                            </div>
                            <div class="col-8">
                                <?php
                                    templatereplace(flattenText($oSurvey->currentLanguageSettings->surveyls_policy_notice));
                                    $fullSurveyDataSecurityNotice = LimeExpressionManager::GetLastPrettyPrintExpression();

                                    $this->widget('ext.admin.TextDisplaySwitch.TextDisplaySwitch', array(
                                        'widgetsJsName' => "security_notice",
                                        'textToDisplay' => $fullSurveyDataSecurityNotice
                                    ));
                            ?>
                            </div>
                        </div>
                    </li>
                    <!-- Data security notice error -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("Data security notice error:");?>
                            </div>
                            <div class="col-8">
                                <?php
                                    templatereplace(flattenText($oSurvey->currentLanguageSettings->surveyls_policy_error));
                                    $fullSurveyDataSecurityNoticeError = LimeExpressionManager::GetLastPrettyPrintExpression();

                                    $this->widget('ext.admin.TextDisplaySwitch.TextDisplaySwitch', array(
                                        'widgetsJsName' => "security_error",
                                        'textToDisplay' => $fullSurveyDataSecurityNoticeError
                                    ));
                            ?>
                            </div>
                        </div>
                    </li>
                    <!-- Data security notice label -->
                    <li class="list-group-item">
                        <div class="ls-flex-row col-12">
                            <div class="col-4">
                                <?php eT("Data security notice label:");?>
                            </div>
                            <div class="col-8">
                                <?php
                                    $dataSecNoticeLabel = Survey::replacePolicyLink($oSurvey->currentLanguageSettings->surveyls_policy_notice_label, $oSurvey->sid);
                                    templatereplace(flattenText($dataSecNoticeLabel));
                                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                                ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?php eT("Survey general settings");?>:</strong>
                <a class="pull-right btn btn-default btn-xs pjax" data-toggle="tooltip" title="<?=gT('General survey settings')?>" href="<?=$this->createUrl('admin/survey/sa/rendersidemenulink', ['subaction'=>'generalsettings', 'surveyid'=>$surveyid])?>">
                    <i class="fa fa-cogs"></i>
                </a>
            </div>
            <div class="row ls-space margin top-10">
        <?php } ?>

        <div class="col-md-12 col-lg-6">
        <?=Yii::app()->twigRenderer->renderViewFromFile('/application/views/admin/survey/subview/surveydashboard/'.$panel, get_defined_vars(), true)?>
        </div>

    <?php } ?>       
</div>
<!-- END surveySummary -->
