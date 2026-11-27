<?php
/* @var $this SurveysGroupsController */

/* @var $model SurveysGroups */
?>
<div class="row">
    <div class="col-12 list-surveys">
        <ul class="nav nav-tabs" id="surveygrouptabsystem" role="tablist" aria-label="<?= gT('Survey group') ?>">
            <?php if ($model->hasPermission('group', 'read')) : ?>
                <li class="nav-item" role="presentation">
                    <a id="settingsForThisGroup-tab" class="nav-link active" href="#settingsForThisGroup" data-bs-toggle="tab" role="tab" aria-controls="settingsForThisGroup" aria-selected="true" tabindex="0">
                        <?php eT('Settings for this survey group'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item" role="presentation">
                <a id="surveysInThisGroup-tab" class="nav-link" href="#surveysInThisGroup" data-bs-toggle="tab" role="tab" aria-controls="surveysInThisGroup" aria-selected="false" tabindex="-1">
                    <?php eT('Surveys in this group'); ?>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a id="templateSettingsFortThisGroup-tab" class="nav-link" href="#templateSettingsFortThisGroup" data-bs-toggle="tab" role="tab" aria-controls="templateSettingsFortThisGroup" aria-selected="false" tabindex="-1">
                    <?php eT('Themes options for this survey group'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <?php if ($model->hasPermission('group', 'read')) : ?>
                <div id="settingsForThisGroup" class="tab-pane show active" role="tabpanel" aria-labelledby="settingsForThisGroup-tab">
                    <?php $this->renderPartial('./surveysgroups/_form', $_data_); ?>
                </div>
            <?php endif; ?>
            <div id="surveysInThisGroup" class="tab-pane" role="tabpanel" aria-labelledby="surveysInThisGroup-tab">
                <div class="list-surveys">
                    <h2><?php eT('Surveys in this group:'); ?></h2>
                    <?php
                    $this->widget(
                        'ext.admin.survey.ListSurveysWidget.ListSurveysWidget',
                        array(
                            'model' => $oSurveySearch,
                            'bRenderSearchBox' => false,
                        )
                    );
                    ?>
                </div>
            </div>
            <div id="templateSettingsFortThisGroup" class="tab-pane" role="tabpanel" aria-labelledby="templateSettingsFortThisGroup-tab">
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
