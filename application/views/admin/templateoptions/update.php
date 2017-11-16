<?php
/* @var $this AdminController */
/* @var $model TemplateOptions */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTemplateOptionsUpdate');

?>

<div class="row h1"><?php echo sprintf(gT('Update template options for %s'),'<em>' . $model->template_name . '</em>'); ?></div>
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
                    echo "<div class='ls-flex-column fill'><h4>".gT('There are no simple options in this template.')."</h4></div>";
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
        <div class="alert alert-info alert-dismissible" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <p><?php eT('All fields below (except CSS framework name) must be either a valid JSON array or the string "inherit".'); ?></p>
        </div>

        <div class="alert alert-warning alert-dismissible" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <p><strong><?php eT('Warning!'); ?></strong> <?php eT("Don't touch the values below unless you know what you're doing."); ?></p>
        </div>

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

        <div class="container-fluid ls-space margin bottom-15">
            <div class="row ls-space margin bottom-15">
                <div class="col-sm-6 h4">
                    <?php printf(gT("Upload a logo (maximum size: %d MB):"),getMaximumFileUploadSize()/1024/1024); ?>
                </div>
                <div class="col-sm-6">
                <?php echo TbHtml::form(array('admin/templates/sa/upload'), 'post', array('id'=>'uploadlogo', 'name'=>'uploadlogo', 'enctype'=>'multipart/form-data')); ?>                        
                    <span id="fileselector">
                        <label class="btn btn-default col-xs-8" for="upload_logo">
                            <input class="hidden" id="upload_logo" name="upload_logo" type="file">
                            <i class="fa fa-upload ls-space margin right-10"></i><?php eT("Upload"); ?>
                        </label>
                    </span>
                    
                        <input type='hidden' name='templatename' value='<?php echo $model->template_name; ?>' />
                        <input type='hidden' name='templateconfig' value='<?php echo $model->id; ?>' />
                        <input type='hidden' name='<?php echo Yii::app()->request->csrfTokenName; ?>' value='<?php echo Yii::app()->request->csrfToken; ?>' />
                        <input type='hidden' name='action' value='templateuploadlogo' />
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="progress">
                    <div id="logo_upload_progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        <span class="sr-only">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <hr/>

        <div class="container-fluid">
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

            <div class="row buttons hidden">
                <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['class'=> 'btn-success']); ?>
            </div>

            <?php $this->endWidget(); ?>
        </div>
    </div>

</div>


<script type="text/javascript">
var progressHandling = function(event){
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    var progress_bar_id = "#logo_upload_progress";
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    // update progressbars classes so it fits your code
    $(progress_bar_id).css("width", String(percent)+"%");
    $(progress_bar_id).find('span.sr-only').text(percent + "%");
}
$(document).on('ready pjax:complete', function(e){
    $('#upload_logo').on('change', function(e){
        e.preventDefault();
        var progress_bar_id = "#logo_upload_progress";
        var formData = new FormData($('#uploadlogo')[0]);

        // add assoc key values, this will be posts values
        // formData.append("file", $('#upload_logo').prop('files')[0]);

        $.ajax({    
            type: "POST",
            url: $('#uploadlogo').attr('action'),
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', progressHandling, false);
                }
                return myXhr;
            },
            success: function (data) {
                console.log(data);
                if(data.success === true){
                    $('#notif-container').append('<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+data.message+'</div>');
                    $(progress_bar_id).css("width", "0%");
                    $(progress_bar_id).find('span.sr-only').text('0%'); 
                } else {
                    $('#notif-container').append('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+data.message+'</div>');
                    $(progress_bar_id).css("width", "0%");
                    $(progress_bar_id).find('span.sr-only').text('0%'); 
                }
            },
            error: function (error) {
                $(progress_bar_id).css("width", "0%");
                $(progress_bar_id).find('span.sr-only').text('0%'); 
                console.log(error);
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    })
});
</script>
