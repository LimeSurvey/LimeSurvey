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
        <?php // Filtering reloads the page: filters.js writes the result into this live region so it gets announced ?>
        <div id="survey-search-status" class="visually-hidden" role="status" aria-live="polite" aria-atomic="true"
             data-message="<?= CHtml::encode($this->getResultMessage()) ?>"></div>
        <div class="d-flex align-items-baseline">

            <!-- select state -->
            <p id="survey_search_all_surveys" class="survey-actionbar-title"><?php eT('All surveys'); ?></p>
            <?php if ($this->onlyfilter) : ?>
                <div class="survey-actionbar-filters">
                    <div class="survey-actionbar-item">
                        <label for="survey_active" class="visually-hidden"><?= gT('Survey status') ?></label>
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

            <?php else : ?>
                <!-- select group -->
                <div class="survey-actionbar-filters">
                    <div class="survey-actionbar-item search-bar">
                        <label for="survey_searched_value" class="visually-hidden"><?= gT('Search surveys') ?></label>
                        <?= $form->textField($this->model, 'searched_value', ['class' => 'form-control survey-actionbar-formfield', 'placeholder' => gT('Search'), 'value' => App()->request->getQuery('Survey')['searched_value'] ?? '', 'id' => 'survey_searched_value']) ?>
                        <i class="ri-search-line"></i>
                    </div>
                    <div class="survey-actionbar-item">
                        <label for="survey_active" class="visually-hidden"><?= gT('Survey status') ?></label>
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
                        <label for="survey_gsid" class="visually-hidden"><?= gT('Survey group') ?></label>
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
            <?php endif; ?>

            <div class="survey-actionbar-actions ms-auto">
                <div class="survey-actionbar-item">
                    <?php if (Permission::model()->hasGlobalPermission('surveys', 'create')) : ?>
                        <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" class="btn btn-outline-info survey-actionbar-button">
                            <i class="ri-add-line"></i>
                            <?= gT('Create survey') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

  <?php if ($this->switch): ?>
                <?php // One of the two views is always chosen: plain buttons, the chosen one is marked as current (not toggle buttons) ?>
                <div class="survey-actionbar-switch" role="group" aria-label="<?= gT('View') ?>">
                    <i class="view-switch ri-grid-fill survey-actionbar-item" data-action="box-widget" role="button"
                       tabindex="0"
                       aria-label="<?= gT('Box view') ?>"
                       <?= $this->viewtype === 'box-widget' ? 'aria-current="true" active' : '' ?>></i>
                    <i class="view-switch ri-menu-line survey-actionbar-item" data-action="list-widget" role="button"
                       tabindex="0"
                       aria-label="<?= gT('List view') ?>"
                       <?= $this->viewtype === 'list-widget' ? 'aria-current="true" active' : '' ?>></i>
                </div>
            <?php endif; ?>


        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>