<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

?>




<div class="container">
    <div class="row h1"><?php eT('Update TemplateOptions for ').$model->id; ?></div>
    <!-- Using bootstrap tabs to differ between just hte options and advanced direct settings -->
    <div class="row">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#simple" aria-controls="home" role="tab" data-toggle="tab"><?php eT('Simple options')?></a></li>
            <li role="presentation"><a href="#advanced" aria-controls="profile" role="tab" data-toggle="tab"><?php eT('Advanced options')?></a></li>
        </ul>
    </div>
    <div class="row">
        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="simple">
                <?php
                    /***
                     * Here we render just the options as a simple form.
                     * On save, the options are parsed to a JSON string and put into the relevant field in the "real" form
                     * before saving that to database.
                     */

                     //First convert options to json and check if it is valid
                     $oOptions = json_decode($model->options);
                     $jsonError = json_last_error();
                     //if it is not valid, render message
                     if($jsonError !== JSON_ERROR_NONE)
                     {
                         //return
                        echo "<div class='ls-flex-column fill'><h4>".gT('There are no simple options in this template')."</h4></div>";
                     } 
                     //if however there is no error in the parsing of the json string go forth and render the form
                     else 
                     {
                        //@TODO create a twiggable view of this!
                        /**
                         * The form element needs to hold teh class "action_update_options_string_form" to be correctly bound
                         * To be able to change the value in the "real" form, the input needs to now what to change.
                         * So the name attribute should contain the object key we want to change
                         */

                        echo $templateOptionPage;
                     }

                     //
                ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="advanced">
                <?php $form=$this->beginWidget('TbActiveForm', array(
                    'id'=>'template-options-form',
                    'enableAjaxValidation'=>false,
                    'htmlOptions' => ['class' => 'form form-horizontal']
                )); ?>
                <p class="note">Fields with <span class="required">*</span> are required.</p>
                <?php echo $form->errorSummary($model); ?>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'templates_name'); ?>
                    <?php echo $form->textField($model,'templates_name',array('size'=>60,'maxlength'=>150)); ?>
                    <?php echo $form->error($model,'templates_name'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'sid'); ?>
                    <?php echo $form->textField($model,'sid'); ?>
                    <?php echo $form->error($model,'sid'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'gsid'); ?>
                    <?php echo $form->textField($model,'gsid'); ?>
                    <?php echo $form->error($model,'gsid'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'uid'); ?>
                    <?php echo $form->textField($model,'uid'); ?>
                    <?php echo $form->error($model,'uid'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'files_css'); ?>
                    <?php echo $form->textArea($model,'files_css',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'files_css'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'files_js'); ?>
                    <?php echo $form->textArea($model,'files_js',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'files_js'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'files_print_css'); ?>
                    <?php echo $form->textArea($model,'files_print_css',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'files_print_css'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'options'); ?>
                    <?php echo $form->textArea($model,'options',array('rows'=>6, 'cols'=>50 )); ?>
                    <?php echo $form->error($model,'options'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'cssframework_name'); ?>
                    <?php echo $form->textField($model,'cssframework_name',array('size'=>45,'maxlength'=>45)); ?>
                    <?php echo $form->error($model,'cssframework_name'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'cssframework_css'); ?>
                    <?php echo $form->textArea($model,'cssframework_css',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'cssframework_css'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'cssframework_js'); ?>
                    <?php echo $form->textArea($model,'cssframework_js',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'cssframework_js'); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($model,'packages_to_load'); ?>
                    <?php echo $form->textArea($model,'packages_to_load',array('rows'=>6, 'cols'=>50)); ?>
                    <?php echo $form->error($model,'packages_to_load'); ?>
                </div>

                <div class="row buttons">
                    <?php echo TbHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class'=> 'btn-success']); ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>
        </div>

    </div>

</div>
