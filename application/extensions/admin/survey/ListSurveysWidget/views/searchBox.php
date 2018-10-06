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
<div class="row">
    <div class="pull-right">
        <div class="form text-right">
            <!-- Begin Form -->
            <?php $form  =  $this->beginWidget('CActiveForm', array(
                'action' => Yii::app()->createUrl($this->formUrl),
                'method' => 'get',
                'htmlOptions'=>array(
                    'class'=>'form-inline',
                ),
            )); ?>

            <!-- search input -->
            <div class="form-group">
                <?php echo $form->label($this->model, 'searched_value', array('label'=>gT('Search:'),'class'=>'control-label')); ?>
                <?php echo $form->textField($this->model, 'searched_value', array('class'=>'form-control')); ?>
            </div>

            <!-- select state -->
            <div class="form-group">
                <?php echo $form->label($this->model, 'active', array('label'=>gT('Status:'),'class'=>'control-label')); ?>
                <select name="active" id='Survey_active' class="form-control">
                    <option value="" <?php if( $this->model->active==""){echo "selected";}?>><?php eT('(Any)');?></option>
                    <option value="Y" <?php if( $this->model->active=="Y"){echo "selected";}?>><?php eT('Active');?></option>
                    <option value="R" <?php if( $this->model->active=="R"){echo "selected";}?>><?php eT('Active and running');?></option>
                    <option value="N" <?php if( $this->model->active=="N"){echo "selected";}?>><?php eT('Inactive');?></option>
                    <option value="E" <?php if( $this->model->active=="E"){echo "selected";}?>><?php eT('Active but expired');?></option>
                    <option value="S" <?php if( $this->model->active=="S"){echo "selected";}?>><?php eT('Active but not yet started');?></option>
                </select>
            </div>


            <!-- select group -->
            <div class="form-group">
                <?php echo $form->label($this->model, 'group', array('label'=>gT('Group:'),'class'=>'control-label')); ?>
                    <select name="gsid" id='Survey_gsid' class="form-control">
                        <option value=""><?php eT('(Any group)');?></option>
                        <?php foreach( SurveysGroups::getSurveyGroupsList() as $gsid=>$group_title): ?>
                            <option value="<?php echo $gsid;?>" <?php if( $gsid == $this->model->gsid){echo 'selected';} ?>>
                                <?php echo flattenText($group_title);?>
                            </option>
                        <?php endforeach?>
                    </select>
            </div>

            <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
            <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/listsurveys');?>" class="btn btn-warning"><?php eT('Reset');?></a>

            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
