<?php
/* @var $this BoxesController */

/* @var $model Box */
/* @var $form CActiveForm */
/* @var $icons_length integer */
/* @var $icons array */
?>
<div class="container">
    <?php $form = $this->beginWidget(
        'TbActiveForm',
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
        <?php echo $form->labelEx($model, 'position', ['class' => 'form-label']); ?>
        <div class=''>
            <?php echo $form->numberField($model, 'position', array('class' => 'form-control', 'aria-required' => 'true')); ?>
        </div>
        <?php if ($form->error($model, 'position')) : ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'position'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'url', ['class' => 'form-label']); ?>
        <div class=''>
            <?php echo $form->textField($model, 'url', array('class' => 'form-control', 'aria-required' => 'true')); ?>
        </div>
        <?php if ($form->error($model, 'url')) : ?>
            <div class=' text-danger'>
                <?php echo $form->error($model, 'url'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'title', ['class' => 'form-label']); ?>
        <div class=''>
            <?php echo $form->textField($model, 'title', array('class' => 'form-control', 'aria-required' => 'true')); ?>
        </div>
        <?php if ($form->error($model, 'title')) : ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'title'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <?php // The icon is chosen with the "Select icon" button, the text field itself is hidden ?>
        <span class="form-label d-block required" id="Box_ico_label">
            <?= CHtml::encode($model->getAttributeLabel('ico')) ?> <span class="required">*</span>
        </span>
        <?php // col-auto: on small screens the button and the chosen icon keep their size and wrap instead of overlapping ?>
        <div class='row align-items-center gy-2'>
            <div class='btn-group col-auto'>
                <button type='button' class='btn btn-outline-secondary dropdown-toggle limebutton form-control' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false' aria-describedby="Box_ico_label">
                    <?= gT('Select icon') ?>
                    <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <?php // One list item per icon, so the number of icons is announced correctly ?>
                    <?php foreach ($icons as $icon) : ?>
                        <?php $iconLabel = Box::getIconLabel($icon['icon']); ?>
                        <li class="d-inline">
                            <a href="#" role="button" class="m-2 option-icon-link" title="<?= CHtml::encode($iconLabel) ?>" aria-label="<?= CHtml::encode($iconLabel) ?>">
                                <span data-icon='<?php echo $icon['icon']; ?>' data-iconId='<?php echo $icon['id']; ?>' data-icon-label="<?= CHtml::encode($iconLabel) ?>" class='option-icon <?php echo $icon['icon']; ?>' aria-hidden="true"></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul></div>
            <div class="col-auto">
                <span><?= gT('Chosen icon:') ?></span>
                <span id="chosen-icon" class="<?= $model->getIconName() ?> text-success" aria-hidden="true"></span>
                <?php // Text alternative of the chosen icon, announced when another icon is chosen ?>
                <span id="chosen-icon-label" class="visually-hidden" role="status"><?= CHtml::encode(Box::getIconLabel($model->getIconName())) ?></span>
            </div>
            <?php echo $form->textField($model, 'ico', array('size' => 60, 'maxlength' => 255, 'class' => 'form-control d-none')); ?>
        </div>

        <?php if ($form->error($model, 'ico')) : ?>
            <div class=" text-danger">
                <?php echo $form->error($model, 'ico'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'desc', ['class' => 'form-label']); ?>
        <div class=''>
            <?php echo $form->textArea($model, 'desc', array('rows' => 6, 'cols' => 50, 'class' => 'form-control', 'aria-required' => 'true')); ?>
        </div>
        <?php if ($form->error($model, 'desc')) : ?>
            <div class=" text-danger" role="alert">
                <?php echo $form->error($model, 'desc'); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page -->
    <div class="mb-3">
        <?php if ($action == 'create') : ?>
            <input name="Box[page]" id="Boxes_page" type="hidden" value="welcome">
        <?php else : ?>
            <?php echo $form->hiddenField($model, 'page', array()); ?>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'usergroup', ['class' => 'form-label']); ?>
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

        <?php if ($form->error($model, 'usergroup')) : ?>
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
