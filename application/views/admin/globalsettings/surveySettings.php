<?php
/* @var $this SurveysGroupsController */

Yii::app()->getClientScript()->registerScript(
    'GlobalSurveySettings', 
    "window.GlobalSideMenuData = ".json_encode($jsData).";", 
    LSYii_ClientScript::POS_BEGIN
);


$optionsOnOff = array(
    'Y' => gT('On','unescaped'),
    'N' => gT('Off','unescaped'),
);

?>

<div class="col-lg-12 list-surveys">
    
    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'returnbutton'=>array(
                'url'=>'admin/index',
                'text'=>gT('Close'),
            ),
            'savebutton' => array(
                'form' => 'survey-settings-form'
            ),
            'saveandclosebutton' => array(
                'form' => 'survey-settings-form'
            )
        )
    )); ?>

    <h3><?php eT('Global survey settings'); ?></h3>

    <div class="row">
        <?php echo CHtml::form(array("admin/globalsettings/sa/surveysettings"), 'post', array('id'=>'survey-settings-form')); ?>    
            <div id="surveySettingsForThisGroup" style="display: flex; flex-wrap:nowrap;">
                <div id="global-sidebar-container">
                    <global-sidemenu />
                </div>
                <div id="pjax-content" class="tab-content col-md-10">
                    <div class="row">
                        <div class="alert alert-info controls col-sm-12" role="alert">
                            <?php eT('All changes of global survey settings will immediately affect all related survey groups and surveys that use inherited values.'); ?>
                        </div>
                    </div>
                    <?php if($partial == '_generaloptions_panel') { ?> 
                        <div id="surveySettingsGeneral" class="row">
                            <?php $this->renderPartial('survey/subview/accordion/_generaloptions_panel', array(
                                    'oSurvey'=>$oSurvey,
                                    'oSurveyOptions' => $oSurvey->oOptionLabels,
                                    'bShowInherited' => $oSurvey->showInherited,
                                    'optionsOnOff' => $optionsOnOff,
                                    'bShowAllOptions' => false,
                                    'users' => $users,
                            )); ?>
                        </div>
                    <?php } else if($partial == '_presentation_panel') { ?> 
                        <div id="surveySettingsPresentation" >
                            <?php $this->renderPartial('survey/subview/accordion/_presentation_panel', array(
                                'oSurvey'=>$oSurvey,
                                'oSurveyOptions' => $oSurvey->oOptionLabels,
                                'bShowInherited' => $oSurvey->showInherited,
                                'optionsOnOff' => $optionsOnOff
                        )); ?>
                        </div>
                    <?php } else if($partial == '_tokens_panel') { ?> 
                        <div id="surveySettingsParticipants" >
                            <?php $this->renderPartial('survey/subview/accordion/_tokens_panel', array(
                                'oSurvey'=>$oSurvey,
                                'oSurveyOptions' => $oSurvey->oOptionLabels,
                                'bShowInherited' => $oSurvey->showInherited,
                                'optionsOnOff' => $optionsOnOff
                        )); ?>
                        </div>
                    <?php } else if($partial == '_notification_panel') { ?> 
                        <div id="surveySettingsNotification" >
                            <?php $this->renderPartial('survey/subview/accordion/_notification_panel', array(
                                'oSurvey'=>$oSurvey,
                                'oSurveyOptions' => $oSurvey->oOptionLabels,
                                'bShowInherited' => $oSurvey->showInherited,
                                'optionsOnOff' => $optionsOnOff,
                                'bShowAllOptions' => false,
                            )); ?>
                        </div>
                    <?php } else if($partial == '_publication_panel') { ?> 
                        <div id="surveySettingsPublication" >
                            <?php $this->renderPartial('survey/subview/accordion/_publication_panel', array(
                                    'oSurvey'=>$oSurvey,
                                    'oSurveyOptions' => $oSurvey->oOptionLabels,
                                    'bShowInherited' => $oSurvey->showInherited,
                                    'optionsOnOff' => $optionsOnOff,
                                    'dateformatdetails' => $aDateFormatDetails,
                                    'bShowAllOptions' => false,
                            )); ?>
                        </div>
                    <?php } ?>
                    <div class="hidden">
                            <?php echo TbHtml::submitButton('Save', array('class'=>'btn btn-success col-md-2 col-sm-4', "id"=>"survey-settings-form")); ?>
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
        $("#survey-settings-form").attr('action',' <?php echo Yii::app()->getController()->createUrl('/admin/globalsettings/sa/surveysettings') ?>'+window.location.hash);
        $(this).tab('show');       
    });

    
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveySettings').find('a[href='+window.location.hash+']').trigger('click');
        }

        $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'survey-settings-form');

    })
</script>
