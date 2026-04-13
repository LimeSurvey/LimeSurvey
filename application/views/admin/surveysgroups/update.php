<?php
/* @var $this SurveysGroupsController */

/* @var $model SurveysGroups */
?>
<div class="row">
    <div class="col-12 list-surveys">
        <ul class="nav nav-tabs" id="surveygrouptabsystem" role="tablist">
            <?php if ($model->hasPermission('group', 'read')): ?>
                <li class="nav-item">
                    <a class="nav-link active" href="#settingsForThisGroup" data-bs-toggle="tab">
                        <?php eT('Settings for this survey group'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="#surveysInThisGroup" data-bs-toggle="tab">
                    <?php eT('Surveys in this group'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#templateSettingsFortThisGroup" data-bs-toggle="tab">
                    <?php eT('Themes options for this survey group'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <?php if ($model->hasPermission('group', 'read')): ?>
                <div id="settingsForThisGroup" class="tab-pane show active">
                    <?php $this->renderPartial('./surveysgroups/_form', $_data_); ?>
                </div>
            <?php endif; ?>
            <div id="surveysInThisGroup" class="tab-pane">
                <div class="list-surveys">
                    <h2><?php eT('Surveys in this group:'); ?></h2>
                    <?php
                    $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget',
                        array(
                            'model' => $oSurveySearch,
                            'bRenderSearchBox' => false,
                        )
                    );
                    ?>
                </div>
            </div>
            <div id="templateSettingsFortThisGroup" class="tab-pane">
                <?php
                if (is_a($templateOptionsModel, 'TemplateConfiguration')) {
                    Yii::app()->getController()->renderPartial(
                        '/themeOptions/surveythemelist',
                        array(
                            'oSurveyTheme' => $templateOptionsModel,
                            'pageSize' => $pageSize,
                            'SurveysGroup' => $model
                        )
                    );
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    $('#surveygrouptabsystem a').click(function (e) {
        var target = $(e.target).attr('href');
        if (target === '#settingsForThisGroup') {
            $('#save-form-button').attr('data-form-id', 'surveys-groups-form').prop('disabled', false);
        } else { //by now on all other tabs, the save button should be disabled, because no use.
            $('#save-form-button').prop('disabled', true);
        }

    });
</script>
