<?php
/* @var $this SurveysGroupsController */

Yii::app()->getClientScript()->registerScript(
    'GlobalSurveySettings',
    "window.GlobalSideMenuData = " . json_encode($jsData) . ";",
    LSYii_ClientScript::POS_BEGIN
);


$optionsOnOff = array(
    'Y' => gT('On', 'unescaped'),
    'N' => gT('Off', 'unescaped'),
);

?>
<div class="position-relative">
    <?php echo CHtml::form(array("admin/globalsettings/sa/surveysettings"), 'post', array('id' => 'survey-settings-form')); ?>
    <div class="d-flex flex-wrap flex-lg-nowrap" id="surveySettingsForThisGroup">
        <div class="" id="global-sidebar-container">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#sidebar" aria-controls="sidebar" role="tab" data-bs-toggle="tab" aria-selected="true"><?php eT("Settings") ?></a></li>
            </ul>
            <div class="tab-content pt-0 h-100">
                <global-sidemenu/>
             </div>
        </div>
        <div id="pjax-content" class="tab-content col-lg-12 flex-lg-shrink-1 ps-4">
            <div class="row">
                <div class="col-12">
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT('All changes of global survey settings will immediately affect all related survey groups and surveys that use inherited values.'),
                        'type' => 'info',
                        'htmlOptions' => ['class' => 'controls']
                    ]);
                    ?>
                </div>
            </div>
            <?php if ($partial === '_generaloptions_panel') { ?>
                <div id="surveySettingsGeneral" class="row ls-settings-wrapper">
                    <?php $this->renderPartial('survey/subview/accordion/_generaloptions_panel',
                        array(
                            'oSurvey' => $oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'bShowAllOptions' => false,
                            'users' => $users,
                        )
                    ); ?>
                </div>
            <?php } elseif ($partial === '_presentation_panel') { ?>
                <div class="ls-settings-wrapper" id="surveySettingsPresentation">
                    <?php $this->renderPartial('survey/subview/accordion/_presentation_panel',
                        array(
                            'oSurvey' => $oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff
                        )
                    ); ?>
                </div>
            <?php } elseif ($partial === '_tokens_panel') { ?>
                <div class="ls-settings-wrapper" id="surveySettingsParticipants">
                    <?php $this->renderPartial('survey/subview/accordion/_tokens_panel',
                        array(
                            'oSurvey' => $oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff
                        )
                    ); ?>
                </div>
            <?php } elseif ($partial === '_notification_panel') { ?>
                <div  class="ls-settings-wrapper" id="surveySettingsNotification">
                    <?php $this->renderPartial('survey/subview/accordion/_notification_panel',
                        array(
                            'oSurvey' => $oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'bShowAllOptions' => false,
                        )
                    ); ?>
                </div>
            <?php } elseif ($partial === '_publication_panel') { ?>
                <div class="ls-settings-wrapper" id="surveySettingsPublication">
                    <?php $this->renderPartial('survey/subview/accordion/_publication_panel',
                        array(
                            'oSurvey' => $oSurvey,
                            'oSurveyOptions' => $oSurvey->oOptionLabels,
                            'bShowInherited' => $oSurvey->showInherited,
                            'optionsOnOff' => $optionsOnOff,
                            'dateformatdetails' => $aDateFormatDetails,
                            'bShowAllOptions' => false,
                        )
                    ); ?>
                </div>
            <?php } ?>
            <div class="d-none">
                <?php echo TbHtml::submitButton('Save', array('class' => 'btn btn-primary col-md-2 col-sm-4', "id" => "survey-settings-form")); ?>
            </div>
        </div>
        <?php echo CHtml::endForm() ?>
    </div>
</div>

<script>

    $('#surveySettings a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $("#survey-settings-form").attr('action', ' <?php echo Yii::app()->getController()->createUrl('/admin/globalsettings/sa/surveysettings') ?>' + window.location.hash);
        $(this).tab('show');
    });


    $(document).on('ready pjax:scriptcomplete', function () {
        if (window.location.hash) {
            $('#surveySettings').find('a[href=' + window.location.hash + ']').trigger('click');
        }

        $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'survey-settings-form');

    })
</script>