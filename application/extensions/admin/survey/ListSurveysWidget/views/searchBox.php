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
    <div class="col-12">
        <?php $form = $this->beginWidget('CActiveForm', ['action' => App()->createUrl($this->formUrl), 'method' => 'get', 'htmlOptions' => ['class' => ''],]); ?>
        <div class="row row-cols-lg-auto g-2 align-items-end mb-3">
            <!-- search input -->
            <div class="col">
                <?= $form->label($this->model, 'searched_value', ['label' => gT('Search:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm']) ?>
                <?= $form->textField($this->model, 'searched_value', ['class' => 'form-control']) ?>
            </div>

            <!-- select state -->
            <div class="col">
                <?= $form->label($this->model, 'active', ['label' => gT('Status:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm']) ?>
                <select name="active" id='Survey_active' class="form-select">
                    <option value="" <?= empty($this->model->active) ? "selected" : '' ?>>
                        <?= gT('(Any)') ?>
                    </option>
                    <option value="Y" <?= $this->model->active === "Y" ? "selected" : '' ?>>
                        <?= gT('Active') ?>
                    </option>
                    <option value="R" <?= $this->model->active === "R" ? "selected" : '' ?>>
                        <?= gT('Active and running') ?>
                    </option>
                    <option value="N" <?= $this->model->active === "N" ? "selected" : '' ?>>
                        <?= gT('Inactive') ?>
                    </option>
                    <option value="E" <?= $this->model->active === "E" ? "selected" : '' ?>>
                        <?= gT('Active but expired') ?>
                    </option>
                    <option value="S" <?= $this->model->active === "S" ? "selected" : '' ?>>
                        <?= gT('Active but not yet started') ?>
                    </option>
                </select>
            </div>

            <!-- select group -->
            <div class="col">
                <?= $form->label($this->model, 'group', ['label' => gT('Group:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm']) ?>
                <select name="gsid" id='Survey_gsid' class="form-select activate-search">
                    <option value=""><?= gT('(Any group)') ?></option>
                    <?php foreach (SurveysGroups::getSurveyGroupsList() as $gsid => $group_title) : ?>
                        <option value="<?= $gsid ?>" <?= ($gsid === $this->model->gsid) ? "selected" : "" ?>><?= CHtml::encode($group_title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <?= CHtml::submitButton(gT('Search', 'unescaped'), ['class' => 'btn btn-secondary']) ?>
            </div>
            <div class="col">
                <a href="<?= Yii::app()->createUrl('surveyAdministration/listsurveys') ?>" class="btn btn-warning">
                    <i class="ri-refresh-line"></i>
                    <?= gT('Reset') ?>
                </a>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>