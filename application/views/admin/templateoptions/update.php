<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

?>




<div class="container">
    <div class="row h1"><?php eT('Update TemplateOptions for '); echo '<em>' . $model->template_name . '</em>'; ?></div>
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
                     if($jsonError !== JSON_ERROR_NONE && $model->options !== 'inherit')
                     {
                         //return
                        echo "<div class='ls-flex-column fill'><h4>".gT('There are no simple options in this template')."</h4></div>";
                     }
                     //if however there is no error in the parsing of the json string go forth and render the form
                     else
                     {
                        /**
                         * The form element needs to hold the class "action_update_options_string_form" to be correctly bound
                         * To be able to change the value in the "real" form, the input needs to now what to change.
                         * So the name attribute should contain the object key we want to change
                         */

                        echo $templateOptionPage;
                     }

                     //
                ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="advanced">
            <?php 
                $actionBaseUrl = 'admin/templateoptions/sa/update/';
                $actionUrlArray = array('id' => $model->id);
                
                if($model->sid) {
                    unset($actionUrlArray['id']); 
                    $actionUrlArray['sid'] = $model->sid; 
                    $actionUrlArray['surveyd'] = $model->sid;
                    $actionUrlArray['gsid'] = $model->gsid;
                    $actionBaseUrl = 'admin/templateoptions/sa/updatesurvey/';
                    }
                if($model->gsid) {
                    unset($actionUrlArray['id']); 
                    $actionBaseUrl = 'admin/templateoptions/sa/updatesurveygroup/';
                    $actionUrlArray['gsid'] = $model->gsid;
                }

                $actionUrl = Yii::app()->getController()->createUrl($actionBaseUrl,$actionUrlArray);
            ?>
                <?php $form=$this->beginWidget('TbActiveForm', array(
                    'id'=>'template-options-form',
                    'enableAjaxValidation'=>false,
                    'htmlOptions' => ['class' => 'form '],
                    'action' => $actionUrl
                )); ?>
                <p class="note">Fields with <span class="required">*</span> are required.</p>
                <?php echo $form->errorSummary($model); ?>


                <?php echo $form->hiddenField($model,'template_name'); ?>
                <?php echo $form->hiddenField($model,'sid'); ?>
                <?php echo $form->hiddenField($model,'gsid'); ?>
                <?php echo $form->hiddenField($model,'uid'); ?>

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

<!-- <script type="text/javascript">
$(document).on('ready pjax:complete', function(e){
    $('.action_activate_bootstrapswitch').bootstrapSwitch();
    if($('.action_update_options_string_form').length > 0){
        var optionObject = {};
        optionObject = JSON.parse($('#TemplateConfiguration_options').val());
        
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            var itemValue = optionObject[$(item).attr('name')];
            $(item).val(itemValue);
            if($(item).attr('type') == 'checkbox' && itemValue !='off') $(item).prop('checked', true).trigger('change');
        })
    }
});
</script> -->
