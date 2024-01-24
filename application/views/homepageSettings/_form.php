<?php
/* @var $this BoxesController */

/* @var $model Box */
/* @var $form CActiveForm */
/* @var $icons_length interger */
/* @var $icons array */
?>
<div class="container">
    <?php $form = $this->beginWidget('TbActiveForm',
        array(
            'id' => 'boxes-form',
            // Please note: When you enable ajax validation, make sure the corresponding
            // controller action is handling ajax validation correctly.
            // There is a call to performAjaxValidation() commented in generated controller code.
            // See class documentation of CActiveForm for details on this.
            'enableAjaxValidation' => false
        )
    ); ?>

    <?php
    $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
    ?>


    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'position'); ?></label>
        <div class=''>
            <?php echo $form->numberField($model, 'position', array('class' => 'form-control')); ?>
        </div>
        <?php if ($form->error($model, 'position')): ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'position'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'url'); ?></label>
        <div class=''>
            <?php echo $form->textField($model, 'url', array('class' => 'form-control')); ?>
        </div>
        <?php if ($form->error($model, 'url')): ?>
            <div class=' text-danger'>
                <?php echo $form->error($model, 'url'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'title'); ?></label>
        <div class=''>
            <?php echo $form->textField($model, 'title', array('class' => 'form-control')); ?>
        </div>
        <?php if ($form->error($model, 'title')): ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'title'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'ico'); ?></label>
        <div class='row align-items-center'>
            <div class='btn-group col-2'>
                <button type='button' class='btn btn-outline-secondary dropdown-toggle limebutton form-control' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <?= gT('Select icon') ?>
                    <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li>
                        <?php foreach ($icons as $icon) : ?>
                            <a href="#" class="m-2">
                                <span data-icon='<?php echo $icon['icon']; ?>' data-iconId='<?php echo $icon['id']; ?>' class='option-icon <?php echo $icon['icon']; ?>'></span>
                            </a>
                        <?php endforeach; ?>
                    </li>
                </ul></div>
            <div class="col-2"><span>&nbsp;<?= gT('Chosen icon:') ?></span>&nbsp;<span id="chosen-icon" class="<?= $model->getIconName() ?> text-success"></span></div>
            <?php echo $form->textField($model, 'ico', array('size' => 60, 'maxlength' => 255, 'class' => 'form-control d-none')); ?>
        </div>

        <?php if ($form->error($model, 'ico')): ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'ico'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'desc'); ?></label>
        <div class=''>
            <?php echo $form->textArea($model, 'desc', array('rows' => 6, 'cols' => 50, 'class' => 'form-control')); ?>
        </div>
        <?php if ($form->error($model, 'desc')): ?>
            <div class=" text-danger" role="alert">
                <?php echo $form->error($model, 'desc'); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page -->
    <div class="mb-3">
        <?php if ($action == 'create'): ?>
            <input name="Box[page]" id="Boxes_page" type="hidden" value="welcome">
        <?php else: ?>
            <?php echo $form->hiddenField($model, 'page', array()); ?>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class='form-label '><?php echo $form->labelEx($model, 'usergroup'); ?></label>
        <div class=''>
            <?php
            $options_array = CHtml::listData(UserGroup::model()->findAll(), 'ugid', 'name');
            $options_array[-1] = gT('Everybody');
            $options_array[-2] = gT('Only admin');
            $options_array[-3] = gT('Nobody');
            ?>
            <?php echo $form->dropDownList(
                $model,
                'usergroup',
                $options_array,
                array(
                    'class' => 'form-select',
                    'options' => array(
                        $model['usergroup'] => array('selected' => true)
                    )
                )
            ); ?>
        </div>

        <?php if ($form->error($model, 'usergroup')): ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'usergroup'); ?>
            </div>
        <?php endif; ?>
    </div>

    <p class="note"><?php printf(gT('Fields with %s*%s are required.'), '<span class="required">', '</span>'); ?></p>
    <div class="mb-3 buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'form-control d-none')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div>
