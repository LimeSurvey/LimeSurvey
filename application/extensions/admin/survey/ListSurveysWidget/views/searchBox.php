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

?>

<!-- Search Box -->

<!-- Begin Form -->
<?php
$form = $this->beginWidget('CActiveForm', array(
    'action' => Yii::app()->createUrl($this->formUrl),
    'method' => 'get',
    'htmlOptions' => array(
        'class' => ''
    ),
)); ?>
<div class="row row-cols-lg-auto g-3 align-items-center mb-3 float-end">
    <!-- search input -->
    <div class="col-12">
        <?php
        echo $form->label(
            $this->model,
            'searched_value',
            array('label' => gT('Search:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
        ); ?>
    </div>
    <div class="col-12">
        <?php
        echo $form->textField($this->model, 'searched_value', array('class' => 'form-control')); ?>
    </div>

    <!-- select state -->
    <div class="col-12">
        <?php
        echo $form->label(
            $this->model,
            'active',
            array('label' => gT('Status:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
        ); ?>
    </div>
    <div class="col-12">
        <select name="active" id='Survey_active' class="form-select">
            <option value="" <?= $this->model->active == "" ? "selected" : '' ?>>
                <?php
                eT('(Any)'); ?>
            </option>
            <option value="Y" <?= $this->model->active == "Y" ? "selected" : '' ?>>
                <?php
                eT('Active'); ?>
            </option>
            <option value="R" <?= $this->model->active == "R" ? "selected" : '' ?>>
                <?php
                eT('Active and running'); ?>
            </option>
            <option value="N" <?= $this->model->active == "N" ? "selected" : '' ?>>
                <?php
                eT('Inactive'); ?>
            </option>
            <option value="E" <?= $this->model->active == "E" ? "selected" : '' ?>>
                <?php
                eT('Active but expired'); ?>
            </option>
            <option value="S" <?= $this->model->active == "S" ? "selected" : '' ?>>
                <?php
                eT('Active but not yet started'); ?>
            </option>
        </select>
    </div>

    <!-- select group -->
    <div class="col-12">
        <?php
        echo $form->label(
            $this->model,
            'group',
            array('label' => gT('Group:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
        ); ?>
    </div>
    <div class="col-12">
        <select name="gsid" id='Survey_gsid' class="form-select">
            <option value=""><?php
                eT('(Any group)'); ?></option>
            <?php
            foreach (SurveysGroups::getSurveyGroupsList() as $gsid => $group_title): ?>
                <option value="<?php
                echo $gsid; ?>" <?php
                if ($gsid == $this->model->gsid) {
                    echo 'selected';
                } ?>>
                    <?php
                    echo flattenText($group_title); ?>
                </option>
            <?php
            endforeach ?>
        </select>
    </div>
    <div class="col-12">
        <?php
        echo CHtml::submitButton(gT('Search', 'unescaped'), array('class' => 'btn btn-success')); ?>
        <a href="<?php
        echo Yii::app()->createUrl('surveyAdministration/listsurveys'); ?>" class="btn btn-warning">
            <span class="fa fa-refresh"></span>
            <?php
            eT('Reset'); ?>
        </a>
    </div>
</div>
<?php
$this->endWidget(); ?>

