<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */

$optionsOnOff = array(
    'Y' => gT('On','unescaped'),
    'N' => gT('Off','unescaped'),
);

Yii::app()->getClientScript()->registerScript(
    'GlobalSurveySettings', 
    "window.GlobalSideMenuData = ".json_encode($jsData).";", 
    LSYii_ClientScript::POS_BEGIN
);

?>

<div class="col-lg-12 list-surveys">

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
    <h3><?php eT('Survey settings for group: '); echo '<strong><em>'.CHtml::encode($model->title).'</strong></em>'; ?></h3>
    <div class="row">
        <div id="surveySettingsForThisGroup" style="display: flex; flex-wrap:nowrap;">
            <div id="global-sidebar-container">
                <global-sidemenu />
            </div>
            <div id="pjax-content" class="tab-content col-md-10">                
            <div class="row">
                <div class="alert alert-info controls col-sm-12" role="alert">
                    <?php eT('All changes of survey group settings will have immediate effect on all related surveys that use inherited values.'); ?>
                </div>
            </div>
            <?php echo CHtml::form(array("admin/surveysgroups/sa/surveysettings/id/".$oSurvey->gsid."/#surveySettingsGeneral"), 'post', array('id'=>'survey-settings-options-form')); ?>    
                <div class="tab-content col-md-10">
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
                            <?php echo TbHtml::submitButton('Save', array('class'=>'btn btn-success col-md-2 col-sm-4', "id"=>"survey-settings-options-form")); ?>
                    </div>
                
                </div>
            </form>
        </div>
    </div>
</div>

<?php
Yii::app()->getClientScript()->registerScript( "editLocalSettings_submit", "

    window.LS.unrenderBootstrapSwitch();
    window.LS.renderBootstrapSwitch();

    $('#surveySettings a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $('#survey-settings-options-form').attr('action',' ".Yii::app()->getController()->createUrl('/admin/surveysgroups/sa/surveysettings/id/'.$oSurvey->gsid)."'+window.location.hash);
        $(this).tab('show');       
    });

    $('.text-option-inherit').on('change', function(e){
        var newValue = $(this).find('.btn.active input').val();
        var parent = $(this).parent().parent();
        var inheritValue = parent.find('.inherit-edit').data('inherit-value');
        var savedValue = parent.find('.inherit-edit').data('saved-value');

        if (newValue == 'Y'){
            parent.find('.inherit-edit').addClass('hide').removeClass('show').val(inheritValue);
            parent.find('.inherit-readonly').addClass('show').removeClass('hide');
        } else {
            var inputValue = (savedValue === inheritValue) ? \"\" : savedValue;
            parent.find('.inherit-edit').addClass('show').removeClass('hide').val(inputValue);
            parent.find('.inherit-readonly').addClass('hide').removeClass('show');
        }
    });

    if(window.location.hash){
        $('#surveySettings').find('a[href='+window.location.hash+']').trigger('click');
    }
    $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'survey-settings-options-form');

", LSYii_ClientScript::POS_POSTSCRIPT);
?>
