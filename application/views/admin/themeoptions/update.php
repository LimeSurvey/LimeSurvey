<?php
/* @var $this AdminController */
/* @var $model TemplateOptions */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTemplateOptionsUpdate');

$gsid = Yii::app()->request->getQuery('gsid', null);
$sid = Yii::app()->request->getQuery('surveyid', null);
?>

<?php if (empty($model->sid)): ?>
<!-- This is only visible when we're not in survey view. -->
<div class='container-fluid'>
    <div class='menubar' id='theme-options-bar'>
        <div class='row'>
            <div class='text-right'>

                <?php
                  $sThemeOptionUrl = App()->createUrl("admin/themeoptions");
                  $sGroupEditionUrl = App()->createUrl("admin/surveysgroups/sa/update", array("id"=>$gsid));

                    $sUrl = (is_null($gsid))?$sThemeOptionUrl:$sGroupEditionUrl;
                ?>
                <a class="btn btn-default" href="<?php echo $sUrl; ?>" role="button">
                    <span class="fa fa-backward"></span>
                    &nbsp;&nbsp;
                    <?php eT('Close'); ?>
                </a>


                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="template-options-form">
                    <span class="fa fa-floppy-o"></span>
                    <?php eT('Save'); ?>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
<div class="col-sm-12 side-body <?=getSideBodyClass(false)?>" id="theme-option-sidebody">
<?php endif; ?>
    <div class="row h1 pagetitle">
        <?php echo sprintf(gT('Survey options for theme %s'),'<em>' . $model->template_name . '</em>'); ?>
        (
            <?php

                // This is a quick and dirty solution.
                // @Todo A clean system to show the level and indicate where the inherited values are taken
                // @Todo: Don't concatenate translations, fix them, Louis

                if (!is_null($sid)){
                    eT(" for survey id: $sid ");
                }elseif(!is_null($gsid)){
                    eT(" for survey group id: $gsid ");
                }else{
                    eT(" global level");
                }

            ?>
        )
    </div>
        <!-- Using bootstrap tabs to differ between just hte options and advanced direct settings -->
    <div class="row">
        <div class="col-sm-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#simple" aria-controls="home" role="tab" data-toggle="tab"><?php eT('Simple options')?></a></li>
                <li role="presentation"><a href="#advanced" aria-controls="profile" role="tab" data-toggle="tab"><?php eT('Advanced options')?></a></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
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
                            echo "<div class='ls-flex-column fill'><h4>".gT('There are no simple options in this survey theme.')."</h4></div>";
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
                        $actionBaseUrl = 'admin/themeoptions/sa/update/';
                        $actionUrlArray = array('id' => $model->id);

                        if($model->sid) {
                            unset($actionUrlArray['id']);
                            $actionUrlArray['sid'] = $model->sid;
                            $actionUrlArray['surveyd'] = $model->sid;
                            $actionUrlArray['gsid'] = $model->gsid;
                            $actionBaseUrl = 'admin/themeoptions/sa/updatesurvey/';
                            }
                        if($model->gsid) {
                            unset($actionUrlArray['id']);
                            $actionBaseUrl = 'admin/themeoptions/sa/updatesurveygroup/';
                            $actionUrlArray['gsid'] = $model->gsid;
                            $actionUrlArray['id'] = $model->id;
                        }

                        $actionUrl = Yii::app()->getController()->createUrl($actionBaseUrl,$actionUrlArray);
                    ?>
                    <div class="container-fluid">
                        <div class="row ls-space margin bottom-15">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6 h4">
                                        <?php printf(gT("Upload an image (maximum size: %d MB):"),getMaximumFileUploadSize()/1024/1024); ?>
                                    </div>
                                    <div class="col-sm-6">
                                    <?php echo TbHtml::form(array('admin/themes/sa/upload'), 'post', array('id'=>'uploadimage', 'name'=>'uploadimage', 'enctype'=>'multipart/form-data')); ?>
                                        <span id="fileselector">
                                            <label class="btn btn-default col-xs-8" for="upload_image">
                                                <input class="hidden" id="upload_image" name="upload_image" type="file" >
                                                <i class="fa fa-upload ls-space margin right-10"></i><?php eT("Upload"); ?>
                                            </label>
                                        </span>

                                            <input type='hidden' name='templatename' value='<?php echo $model->template_name; ?>' />
                                            <input type='hidden' name='templateconfig' value='<?php echo $model->id; ?>' />
                                            <input type='hidden' name='action' value='templateuploadimagefile' />
                                        </form>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="progress">
                                        <div id="upload_progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                            <span class="sr-only">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr/>
                        </div>
                        <div class="row">
                            <div class="container-fluid">
                                <?php $form=$this->beginWidget('TbActiveForm', array(
                                    'id'=>'template-options-form',
                                    'enableAjaxValidation'=>false,
                                    'htmlOptions' => ['class' => 'form '],
                                    'action' => $actionUrl
                                )); ?>
                                <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
                                <?php echo $form->errorSummary($model); ?>


                                <?php echo $form->hiddenField($model,'template_name'); ?>
                                <?php echo $form->hiddenField($model,'sid'); ?>
                                <?php echo $form->hiddenField($model,'gsid'); ?>
                                <?php echo $form->hiddenField($model,'uid'); ?>

                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'files_css'); ?>
                                        <?php echo $form->textArea($model,'files_css',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'files_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'files_js'); ?>
                                        <?php echo $form->textArea($model,'files_js',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'files_js'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'files_print_css'); ?>
                                        <?php echo $form->textArea($model,'files_print_css',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'files_print_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'options'); ?>
                                        <?php echo $form->textArea($model,'options',array('rows'=>6, 'cols'=>50 )); ?>
                                        <?php echo $form->error($model,'options'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'cssframework_name'); ?>
                                        <?php echo $form->textField($model,'cssframework_name',array('size'=>45,'maxlength'=>45)); ?>
                                        <?php echo $form->error($model,'cssframework_name'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'cssframework_css'); ?>
                                        <?php echo $form->textArea($model,'cssframework_css',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'cssframework_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'cssframework_js'); ?>
                                        <?php echo $form->textArea($model,'cssframework_js',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'cssframework_js'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model,'packages_to_load'); ?>
                                        <?php echo $form->textArea($model,'packages_to_load',array('rows'=>6, 'cols'=>50)); ?>
                                        <?php echo $form->error($model,'packages_to_load'); ?>
                                    </div>
                                </div>
                                <div class="row buttons hidden">
                                    <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['class'=> 'btn-success']); ?>
                                </div>

                                <?php $this->endWidget(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
Yii::app()->getClientScript()->registerScript("themeoptions-scripts", '

        var bindUpload = function(options){
            var $activeForm = $(options.form);
            var $activeInput = $(options.input);
            var $progressBar = $(options.progress);

            var onSuccess = options.onSuccess || function(){};
            var onBeforeSend = options.onBeforeSend || function(){};

            var progressHandling = function(event){
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
                // update progressbars classes so it fits your code
                $progressBar.css("width", String(percent)+"%");
                $progressBar.find(\'span.sr-only\').text(percent + "%");
            };

            $activeInput.on(\'change\', function(e){
                e.preventDefault();
                var formData = new FormData($activeForm[0]);
                console.log(JSON.stringify(formData));
                // add assoc key values, this will be posts values
                formData.append("file", $activeInput.prop(\'files\')[0]);

                $.ajax({
                    type: "POST",
                    url: $activeForm.attr(\'action\'),
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener(\'progress\', progressHandling, false);
                        }
                        return myXhr;
                    },
                    beforeSend : onBeforeSend,
                    success: function (data) {
                        console.log(data);
                        if(data.success === true){
                            $(\'#notif-container\').append(\'<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\'+data.message+\'</div>\');
                            $progressBar.css("width", "0%");
                            $progressBar.find(\'span.sr-only\').text(\'0%\');
                            onSuccess();
                        } else {
                            $(\'#notif-container\').append(\'<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\'+data.message+\'</div>\');
                            $progressBar.css("width", "0%");
                            $progressBar.find(\'span.sr-only\').text(\'0%\');
                        }
                    },
                    error: function (error) {
                        $progressBar.css("width", "0%");
                        $progressBar.find(\'span.sr-only\').text(\'0%\');
                        console.log(error);
                    },
                    async: true,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000
                });
            });
            return this;
        };

        $(document).on(\'ready pjax:scriptcomplete\', function(){
            $("#theme-option-sidebody").height($("#advanced").height()+200);
            var uploadImageBind = new bindUpload({
                form: \'#uploadimage\',
                input: \'#upload_image\',
                progress: \'#upload_progress\'
            });
        });
    ', LSYii_ClientScript::POS_END);
?>
