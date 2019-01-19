<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */

$optionsOnOff = array(
    'Y' => gT('On','unescaped'),
    'N' => gT('Off','unescaped'),
);

?>

<div class="col-lg-12 list-surveys">

    <h3><?php eT('Survey settings for group: '); echo '<strong><em>'.$model->title.'</strong></em>'; ?></h3>
    <?php $this->renderPartial('super/fullpagebar_view', array(
            'fullpagebar' => array(
                'returnbutton'=>array(
                    'url'=>'admin/survey/sa/listsurveys#surveygroups',
                    'text'=>gT('Close'),
                ),
                'savebutton' => array(
                    'form' => 'survey-settings-options-form'
                ),
                'saveandclosebutton' => array(
                    'form' => 'survey-settings-options-form'
                )
            )
        )); ?>

    <div class="row">
        <div id="surveySettingsForThisGroup">
            <?php echo CHtml::form(array("admin/surveysgroups/sa/surveysettings/id/".$oSurvey->gsid."/#surveySettingsGeneral"), 'post', array('id'=>'survey-settings-options-form')); ?>    
                <ul class="nav nav-pills nav-stacked col-md-2" id="surveySettings" role="tablist">
                    <li class="active"><a href="#surveySettingsGeneral"><?php eT('General settings'); ?></a></li>
                    <li><a href="#surveySettingsPresentation"><?php eT('Presentation & navigation settings'); ?></a></li>
                    <li><a href="#surveySettingsParticipants"><?php eT('Survey participant settings'); ?></a></li>
                    <li><a href="#surveySettingsNotification"><?php eT('Notification and data management settings'); ?></a></li>
                    <li><a href="#surveySettingsPublication"><?php eT('Publication & access control settings'); ?></a></li>
                </ul>
                <div class="tab-content col-md-10">
                
                    <div id="surveySettingsGeneral" class="tab-pane active">                                        
                        <?php $this->renderPartial('survey/subview/accordion/_generaloptions_panel', array(
                            'oSurvey'=>$oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'bShowAllOptions' => false,
                            'users' => $users,
                            
                            )); ?>
                    </div>

                    <div id="surveySettingsPresentation" class="tab-pane">                                        
                        <?php $this->renderPartial('survey/subview/accordion/_presentation_panel', array(
                            'oSurvey'=>$oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff
                            )); ?>
                    </div>

                    <div id="surveySettingsParticipants" class="tab-pane">
                        <?php $this->renderPartial('survey/subview/accordion/_tokens_panel', array(
                            'oSurvey'=>$oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff
                            )); ?>
                    </div>

                    <div id="surveySettingsNotification" class="tab-pane">
                        <?php $this->renderPartial('survey/subview/accordion/_notification_panel', array(
                            'oSurvey'=>$oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'bShowAllOptions' => false,
                            )); ?>
                    </div>

                    <div id="surveySettingsPublication" class="tab-pane">
                        <?php $this->renderPartial('survey/subview/accordion/_publication_panel', array(
                            'oSurvey'=>$oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'dateformatdetails' => $aDateFormatDetails,
                            'bShowAllOptions' => false,
                            )); ?>
                    </div>

                    <div class="hidden">
                            <?php echo TbHtml::submitButton('Save', array('class'=>'btn btn-success col-md-2 col-sm-4', "id"=>"survey-settings-options-form")); ?>
                    </div>
                
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    window.LS.unrenderBootstrapSwitch();
    window.LS.renderBootstrapSwitch();

    $('#surveySettings a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $("#survey-settings-options-form").attr('action',' <?php echo Yii::app()->getController()->createUrl('/admin/surveysgroups/sa/surveysettings/id/'.$oSurvey->gsid) ?>'+window.location.hash);
        $(this).tab('show');       
    });

    
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveySettings').find('a[href='+window.location.hash+']').trigger('click');
        }

        $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'survey-settings-options-form');

    })
</script>
