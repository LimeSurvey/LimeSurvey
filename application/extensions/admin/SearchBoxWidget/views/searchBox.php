<?php
/**
 * This template renders a search box and action bar for managing surveys view type.
 * It includes filters for survey status, group selection, and a search field.
 * Additionally, it provides options for creating new surveys and survey groups if the user has the appropriate permissions.
 *
 * @var CActiveForm $form The form widget used for submitting search and filter parameters.
 */
?>
<!-- Search Box -->
<!-- Begin Form -->
<div class="row">
    <div class="survey-actionbar col-12">
        <?php $form = $this->beginWidget('CActiveForm', ['action' => $this->formUrl, 'method' => 'get', 'id' => 'survey-search']); ?>
        <div class="d-flex align-items-baseline">

            <!-- select state -->
            <p class="survey-actionbar-title"><?php eT('All surveys'); ?></p>
            <?php if ($this->onlyfilter) : ?>
                <div class="survey-actionbar-filters">
                    <div class="survey-actionbar-item">
                        <select name="active" id='survey_active' class="form-select survey-actionbar-formfield">
                            <option value="" <?= empty(App()->request->getQuery('active')) ? "selected" : '' ?>>
                                <?= gT('Status') ?>
                            </option>
                            <option value="Y" <?= App()->request->getQuery('active') === "Y" ? "selected" : '' ?>>
                                <?= gT('Active') ?>
                            </option>
                            <option value="R" <?= App()->request->getQuery('active') === "R" ? "selected" : '' ?>>
                                <?= gT('Running') ?>
                            </option>
                            <option value="N" <?= App()->request->getQuery('active') === "N" ? "selected" : '' ?>>
                                <?= gT('Inactive') ?>
                            </option>
                            <option value="E" <?= App()->request->getQuery('active') === "E" ? "selected" : '' ?>>
                                <?= gT('Expired') ?>
                            </option>
                            <option value="S" <?= App()->request->getQuery('active') === "S" ? "selected" : '' ?>>
                                <?= gT('Not started') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="survey-actionbar-actions ms-auto">
                    <div class="survey-actionbar-item">
                        <?php if (Permission::model()->hasGlobalPermission('surveys', 'create')) : ?>
                            <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" id="createSurvey" class="btn btn-outline-info survey-actionbar-button">
                                <i class="ri-add-line"></i>
                                <?= gT('Create survey') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="survey-actionbar-item">
                        <?php if (\Permission::model()->hasGlobalPermission('surveysgroups', 'create')) : ?>
                            <a href="<?= Yii::app()->createUrl('admin/surveysgroups/sa/create') ?>" id="createSurveyGroup" class="btn btn-outline-g-700 survey-actionbar-button">
                                <i class="ri-add-line"></i>
                                <?= gT('Create survey group') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else : ?>
                <!-- select group -->
                <div class="survey-actionbar-filters">
                    <div class="survey-actionbar-item search-bar">
                        <?= $form->textField($this->model, 'searched_value', ['class' => 'form-control survey-actionbar-formfield', 'placeholder' => 'Search', 'value' => App()->request->getQuery('Survey')['searched_value'] ?? '']) ?>
                        <i class="ri-search-line"></i>
                    </div>
                    <div class="survey-actionbar-item">
                        <select name="active" id='survey_active' class="form-select survey-actionbar-formfield">
                            <option value="" <?= empty(App()->request->getQuery('active')) ? "selected" : '' ?>>
                                <?= gT('Status') ?>
                            </option>
                            <option value="Y" <?= App()->request->getQuery('active') === "Y" ? "selected" : '' ?>>
                                <?= gT('Active') ?>
                            </option>
                            <option value="R" <?= App()->request->getQuery('active') === "R" ? "selected" : '' ?>>
                                <?= gT('Running') ?>
                            </option>
                            <option value="N" <?= App()->request->getQuery('active') === "N" ? "selected" : '' ?>>
                                <?= gT('Inactive') ?>
                            </option>
                            <option value="E" <?= App()->request->getQuery('active') === "E" ? "selected" : '' ?>>
                                <?= gT('Expired') ?>
                            </option>
                            <option value="S" <?= App()->request->getQuery('active') === "S" ? "selected" : '' ?>>
                                <?= gT('Not started') ?>
                            </option>
                        </select>
                    </div>
                    <div class="survey-actionbar-item">
                        <select name="gsid" id='survey_gsid' class="form-select survey-actionbar-formfield">
                            <option value=""><?= gT('Group') ?></option>
                            <?php foreach (SurveysGroups::getSurveyGroupsList() as $gsid => $group_title) : ?>
                                <option value="<?= $gsid ?>" <?= (App()->request->getQuery('gsid') == $gsid) ? "selected" : "" ?>><?= CHtml::encode($group_title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="survey-actionbar-item">
                        <button id="survey_reset"
                                class="btn btn-outline-g-700 btn-sm survey-actionbar-button <?= !empty(App()->request->getParam('Survey')) ? '' : 'd-none' ?>">
                            <i class="ri-refresh-line"></i>
                            <?= gT('Reset') ?>
                        </button>
                    </div>
                </div>

                <div class="survey-actionbar-actions ms-auto">
                    <div class="survey-actionbar-item">
                        <?php if (Permission::model()->hasGlobalPermission('surveys', 'create')) : ?>
                            <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" class="btn btn-outline-info survey-actionbar-button">
                                <i class="ri-add-line"></i>
                                <?= gT('Create survey') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="survey-actionbar-item">
                        <?php if (Permission::model()->hasGlobalPermission('surveysgroups', 'create')) : ?>
                            <a href="<?= Yii::app()->createUrl('admin/surveysgroups/sa/create') ?>" class="btn btn-outline-g-700 survey-actionbar-button">
                                <i class="ri-add-line"></i>
                                <?= gT('Create survey group') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->switch) : ?>
                <div class="survey-actionbar-switch">
                    <i class="view-switch ri-grid-fill survey-actionbar-item" data-action="box-widget" <?= $this->viewtype === 'box-widget' ? 'active' : '' ?>></i>
                    <i class="view-switch ri-menu-line survey-actionbar-item" data-action="list-widget" <?= $this->viewtype === 'list-widget' ? 'active' : '' ?>></i>
                </div>
            <?php endif; ?>

        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
