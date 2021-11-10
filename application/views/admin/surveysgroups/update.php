<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
?>

<div class="col-lg-12 list-surveys">

    <div class="row">
        <ul class="nav nav-tabs" id="surveygrouptabsystem" role="tablist">
            <li class="active">
                <a href="#surveysInThisGroup">
                    <?php eT('Surveys in this group'); ?>
                </a>
            </li>
            <?php if($model->hasPermission('group','read')):?>
                <li>
                    <a href="#settingsForThisGroup">
                        <?php eT('Settings for this survey group'); ?>
                    </a>
                </li>
            <?php endif;?>
            <li>
                <a href="#templateSettingsFortThisGroup">
                    <?php eT('Themes options for this survey group'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="surveysInThisGroup" class="tab-pane active">
                <div class="list-surveys">
                    <h2><?php eT('Surveys in this group:'); ?></h2>
                    <?php
                        $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                                    'model'            => $oSurveySearch,
                                    'bRenderSearchBox' => false,
                                ));
                    ?>
                </div>
            </div>
            <?php if($model->hasPermission('group','read')):?>
                <div id="settingsForThisGroup" class="tab-pane">
                    <?php $this->renderPartial('./surveysgroups/_form', $_data_); ?>
                </div>
            <?php endif;?>
            <div id="templateSettingsFortThisGroup" class="tab-pane">
                <?php
                    if (is_a($templateOptionsModel, 'TemplateConfiguration')){
                        Yii::app()->getController()->renderPartial('/themeOptions/surveythemelist', array( 'oSurveyTheme'=> $templateOptionsModel, 'pageSize'=>$pageSize ));
                    }
                ?>
            </div>

        </div>
    </div>
</div>
<script>

    $('#surveygrouptabsystem a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');

        var target = $(e.target).attr('href');
        if (target == '#surveysInThisGroup') {
            $(this).tab('show');
        } else if (target == '#settingsForThisGroup') {
            $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'surveys-groups-form');
            $(this).tab('show');
        } else if (target == '#securityForThisGroup') {
            $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'surveys-groups-permission');
            $(this).tab('show');
        } else if (target == '#templateSettingsFortThisGroup') {
            $('#save-form-button, #save-and-close-form-button').attr('data-form-id', 'template-options-form');
            $(this).tab('show');
        }
    });
</script>
