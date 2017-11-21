<?php
/* @var $this BoxesController */
/* @var $model Boxes */
/* @var $form CActiveForm */
/* @var $icons_length interger */
/* @var $icons array */
?>
<div class="container container-center">
            <?php $form=$this->beginWidget('TbActiveForm', array(
                'id'=>'boxes-form',
                // Please note: When you enable ajax validation, make sure the corresponding
                // controller action is handling ajax validation correctly.
                // There is a call to performAjaxValidation() commented in generated controller code.
                // See class documentation of CActiveForm for details on this.
                'enableAjaxValidation'=>false
            )); ?>
                <p class="note"><?php printf(gT('Fields with %s*%s are required.'),'<span class="required">','</span>'); ?></p>

                <?php if($form->errorSummary($model)):?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $form->errorSummary($model); ?>
                    </div>
                <?php endif;?>


                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'position'); ?></label>
                    <div class=''>
                        <?php echo $form->numberField($model,'position', array('class' => 'form-control')); ?>
                    </div>
                    <?php if($form->error($model,'position')):?>
                        <div class=" text-danger">
                            <?php echo $form->error($model,'position'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'url'); ?></label>
                    <div class=''>
                        <?php echo $form->textField($model,'url',array('class' => 'form-control')); ?>
                    </div>
                    <?php if($form->error($model,'url')):?>
                        <div class=' text-danger'>
                            <?php echo $form->error($model,'url'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'title'); ?></label>
                    <div class=''>
                        <?php echo $form->textField($model,'title',array('class' => 'form-control')); ?>
                    </div>
                    <?php if($form->error($model,'title')):?>
                        <div class=" text-danger">
                            <?php echo $form->error($model,'title'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'ico'); ?></label>
                    <div class=''>
                        <div class='btn-group'>
                            <button type='button' class='btn btn-default dropdown-toggle limebutton form-control' data-toggle='dropdown' aria-hashpopup='true' aria-expanded='false'>
                                <?php eT('Select icon'); ?>
                                <span class='caret'></span>
                            </button>
                            <ul class='dropdown-menu'>
                                <li>
                                <div class='row' style='width: 400px;'>
                                    <div class=''>
                                        <ul class='list-unstyled'>
                                            <?php for ($i = 0; $i < $icons_length / 3; $i++): ?>
                                                 <li class='icon-'><a href="#"><span data-icon='<?php echo $icons[$i]; ?>' class='option-icon <?php echo $icons[$i]; ?>'></span></a></li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                    <div class=''>
                                        <ul class='list-unstyled'>
                                            <?php for ($i = $icons_length / 3; $i < $icons_length / 3 + $icons_length / 3; $i++): ?>
                                                <li class='icon-'><a href="#"><span data-icon='<?php echo $icons[$i]; ?>' class='option-icon <?php echo $icons[$i]; ?>'></span></a></li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                    <div class=''>
                                        <ul class='list-unstyled'>
                                            <?php for ($i = $icons_length / 3 + $icons_length / 3; $i < $icons_length; $i++): ?>
                                                <li class='icon-'><a href="#"><span data-icon='<?php echo $icons[$i]; ?>' class='option-icon <?php echo $icons[$i]; ?>'></span></a></li>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                </div>
                                </li>
                            </ul>
                        </div>
                        <span>&nbsp;<?php echo eT('Chosen icon:'); ?></span>&nbsp;<span id='chosen-icon'></span>
                        <?php echo $form->textField($model,'ico',array('size'=>60,'maxlength'=>255, 'class' => 'form-control hidden')); ?>
                    </div>

                    <?php if ($form->error($model,'ico')):?>
                        <div class=" text-danger">
                            <?php echo $form->error($model,'ico'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'desc'); ?></label>
                    <div class=''>
                        <?php echo $form->textArea($model,'desc',array('rows'=>6, 'cols'=>50, 'class' => 'form-control')); ?>
                    </div>
                    <?php if($form->error($model,'desc')):?>
                        <div class=" text-danger" role="alert">
                            <?php echo $form->error($model,'desc'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <!-- Page -->
                <div class="form-group">
                    <?php if($action=='create'): ?>
                        <input name="Boxes[page]" id="Boxes_page" type="hidden" value="welcome">
                    <?php else:?>
                        <?php echo $form->hiddenField($model,'page',array()); ?>
                    <?php endif;?>
                </div>

                <div class="form-group">
                    <label class='control-label '><?php echo $form->labelEx($model,'usergroup'); ?></label>
                    <div class=''>
                        <?php
                            $options_array = CHtml::listData(UserGroup::model()->findAll(), 'ugid', 'name');
                            $options_array[-1]=gT('Everybody');
                            $options_array[-2]=gT('Only admin');
                            $options_array[-3]=gT('Nobody');
                        ?>
                        <?php echo $form->dropDownList(
                            $model,
                            'usergroup',
                            $options_array,
                            array(
                                'class' => 'form-control',
                                'options' => array(
                                    $model['usergroup'] => array('selected' => true)
                                )
                            )
                        ); ?>
                    </div>

                    <?php if($form->error($model,'usergroup')):?>
                        <div class=" text-danger">
                            <?php echo $form->error($model,'usergroup'); ?>
                        </div>
                    <?php endif;?>
                </div>

                <div class="form-group buttons">
                    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'form-control hidden')); ?>
                </div>

            <?php $this->endWidget(); ?>
</div>
