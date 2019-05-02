<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
?>

<div class="col-lg-12 list-surveys">

    <h3><?php eT('Update survey group: '); echo '<strong><em>'.CHtml::encode($model->title).'</strong></em>'; ?></h3>
    <?php $this->renderPartial('super/fullpagebar_view', array(
            'fullpagebar' => array(
                'returnbutton'=>array(
                    'url'=>'admin/survey/sa/listsurveys#surveygroups',
                    'text'=>gT('Close'),
                ),
                'savebutton' => array(
                    'form' => 'surveys-groups-form'
                )
            )
        )); ?>

    <div class="row">
        <ul class="nav nav-tabs" id="surveygrouptabsystem" role="tablist">
            <li class="active"><a href="#surveysInThisGroup"><?php eT('Surveys in this group'); ?></a></li>
            <li><a href="#settingsForThisGroup"><?php eT('Settings for this survey group'); ?></a></li>
            <li><a href="#templateSettingsFortThisGroup"><?php eT('Themes options for this survey group'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div id="surveysInThisGroup" class="tab-pane active">
                <div class="col-sm-12 list-surveys">
                    <h2><?php eT('Surveys in this group:'); ?></h2>
                    <?php
                        $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                                    'model'            => $oSurveySearch,
                                    'bRenderSearchBox' => false,
                                ));
                    ?>
                </div>
            </div>
            <div id="settingsForThisGroup" class="tab-pane">
                <?php $this->renderPartial('./surveysgroups/_form', array('model'=>$model)); ?>
            </div>
            <div id="templateSettingsFortThisGroup" class="tab-pane">
                <?php
                    if (is_a($templateOptionsModel, 'TemplateConfiguration')){
                         $this->renderPartial('themeoptions/surveythemelist', array( 'oSurveyTheme'=> $templateOptionsModel, 'pageSize'=>$pageSize )); 
                    }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    $('#surveygrouptabsystem a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $(this).tab('show');
        console.log($(this).attr('href'));
        if($(this).attr('href') == '#templateSettingsFortThisGroup'){
            $('#save-form-button').attr('data-form-id', 'template-options-form');
        } else {
            $('#save-form-button').attr('data-form-id', 'surveys-groups-form');

        }
    });
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveysystem').find('a[href='+window.location.hash+']').trigger('click');
        }
    })
</script>
