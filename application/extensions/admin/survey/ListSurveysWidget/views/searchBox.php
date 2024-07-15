<?php
/*
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * @var $this ListSurveysWidget
 */
?>

<!-- Search Box -->

<!-- Begin Form -->
<div class="row">
    <div class="menu col-12">
        <?php $form = $this->beginWidget('CActiveForm', ['action' => App()->createUrl($this->formUrl), 'method' => 'get', 'htmlOptions' => ['id' => 'survey-search'],]); ?>
        <div class="row">

            <!-- select state -->
            <div class="col-1">
                <h2><?php eT('All surveys'); ?></h2>
            </div>

            <!-- select group -->
            <div class="col-6">
                <div  class="pull-right">

                    <div class="search-bar">
                        <?= $form->textField($this->model, 'searched_value', ['class' => 'form-control', 'placeholder' => 'Search']) ?>
                        <i class="ri-search-line"></i>
                    </div>
                    <div class="dropdown">
                        <select name="active" id='survey_active' class="form-select">
                            <option value="" <?= empty($this->model->active) ? "selected" : '' ?>>
                                <?= gT('Status') ?>
                            </option>
                            <option value="Y" <?= $this->model->active === "Y" ? "selected" : '' ?>>
                                <?= gT('Active') ?>
                            </option>
                            <option value="R" <?= $this->model->active === "R" ? "selected" : '' ?>>
                                <?= gT('Running') ?>
                            </option>
                            <option value="N" <?= $this->model->active === "N" ? "selected" : '' ?>>
                                <?= gT('Inactive') ?>
                            </option>
                            <option value="E" <?= $this->model->active === "E" ? "selected" : '' ?>>
                                <?= gT('Expired') ?>
                            </option>
                            <!--                            <option value="S" --><?php //= $this->model->active === "S" ? "selected" : '' ?><!-->-->
                            <!--                                --><?php //= gT('Active but not yet started') ?>
                            <!--                            </option>-->
                        </select>
                    </div>
                    <div class="dropdown">
                        <select name="gsid" id='survey_gsid' class="form-select">
                            <option value=""><?= gT('Group') ?></option>
                            <?php foreach (SurveysGroups::getSurveyGroupsList() as $gsid => $group_title) : ?>
                                <option value="<?= $gsid ?>" <?= ($gsid === $this->model->gsid) ? "selected" : "" ?>><?= CHtml::encode($group_title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button id="survey_reset" class="btn btn-outline-secondary menu-button b-none <?= !empty(App()->request->getParam('Survey')) ? '' : 'd-none' ?>">
                        <i class="ri-refresh-line"></i>
                        <?= gT('Reset') ?>
                    </button>

                </div>
            </div>

            <div class="col-4">
                <div class="pull-right">
                    <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" class="btn btn-outline-secondary menu-button purple purple-bg">
                        <i class="ri-add-line"></i>
                        <?= gT('Create survey') ?>
                    </a>
                    <a href="<?= Yii::app()->createUrl('admin/surveysgroups/sa/create') ?>" class="btn btn-outline-secondary menu-button">
                        <i class="ri-add-line"></i>
                        <?= gT('Create survey group') ?>
                    </a>
                </div>
            </div>
            <div class="col-1">
                <div class="pull-right menu-icon">
                    <i class="ri-grid-fill"></i>
                    <i class="ri-menu-line purple"></i>
                </div>
            </div>

        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
