<?php
/* @var ThemeOptionsController $this */
/* @var TemplateConfiguration $model */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTemplateOptionsUpdate');

?>
<?php if (empty($model->sid)) : ?>
    <!-- This is only visible when we're not in survey view. -->
    <div class='menubar surveybar' id='theme-options-bar'>
        <div class='row'>
            <div class='text-right'>

                <?php
                $sThemeOptionUrl = App()->createUrl("themeOptions");
                $sGroupEditionUrl = App()->createUrl("admin/surveysgroups/sa/update", ["id" => $gsid, "#" => 'templateSettingsFortThisGroup']);
                $sUrl = (is_null($gsid)) ? $sThemeOptionUrl : $sGroupEditionUrl;
                ?>

                <!-- Back -->
                <a class="btn btn-default" href="<?php echo $sUrl; ?>" role="button">
                    <span class="fa fa-backward"></span>
                    <?php eT('Back'); ?>
                </a>

                <!-- Save -->
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="template-options-form" style="margin-right: 30px;">
                    <span class="fa fa-floppy-o"></span>
                    <?php eT('Save'); ?>
                </a>
            </div>
        </div>
    </div>
<?php else : ?>
<div class="col-sm-12 side-body <?= getSideBodyClass(false) ?>" id="theme-option-sidebody">
<?php endif; ?>
    <!-- Using bootstrap tabs to differ between just hte options and advanced direct settings -->
    <div class="row">
        <div class="col-sm-12" id="theme-options-tabs">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <?php
                if ($aOptionAttributes['optionsPage'] == 'core') {
                    foreach ($aOptionAttributes['categories'] as $key => $category) { ?>
                        <li role="presentation" class="<?php echo $key == 0 ? 'active' : 'action_hide_on_inherit'; ?>"><a href="#category-<?php echo $key; ?>" aria-controls="category-<?php echo $key; ?>" role="tab" data-toggle="tab"><?php echo $category; ?></a></li>
                    <?php } ?>
                <?php } else { ?>
                    <li role="presentation" class="active"><a href="#simple" aria-controls="home" role="tab" data-toggle="tab"><?php eT('Simple options') ?></a></li>
                <?php } ?>
                <li role="presentation"><a href="#advanced" aria-controls="profile" role="tab" data-toggle="tab" class="<?php echo Yii::app()->getConfig('debug') > 1 ? '' : 'hidden'; ?>"><?php eT('Advanced options') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <!-- Tab panes -->
            <?php /* Begin theme option form */ ?>
            <form class='form action_update_options_string_form' action=''>
                <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['id' => 'theme-options--submit', 'class' => 'hidden action_update_options_string_button']); ?>
                <div class="tab-content">
                    <?php
                    /*
                     * Here we render just the options as a simple form.
                     * On save, the options are parsed to a JSON string and put into the relevant field in the "real" form
                     * before saving that to database.
                     */

                    //First convert options to json and check if it is valid
                    $oOptions = json_decode($model->options);
                    $jsonError = json_last_error();
                    //if it is not valid, render message
                    if ($jsonError !== JSON_ERROR_NONE && $model->options !== 'inherit') {
                        //return
                        echo "<div class='ls-flex-column fill'><h4>" . gT('There are no simple options in this survey theme.') . "</h4></div>";
                    } else {
                        //if however there is no error in the parsing of the json string go forth and render the form
                        /*
                         * The form element needs to hold the class "action_update_options_string_form" to be correctly bound
                         * To be able to change the value in the "real" form, the input needs to now what to change.
                         * So the name attribute should contain the object key we want to change
                         */

                        if ($aOptionAttributes['optionsPage'] == 'core') {
                            $this->renderPartial(
                                './options_core',
                                [
                                    'aOptionAttributes'      => $aOptionAttributes,
                                    'aTemplateConfiguration' => $aTemplateConfiguration,
                                    'oParentOptions'         => $oParentOptions,
                                    'sPackagesToLoad'        => $sPackagesToLoad
                                ]
                            );
                        } else {
                            echo '<div role="tabpanel" class="tab-pane active" id="simple">';
                            echo $templateOptionPage;
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </form>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?php echo Yii::app()->getConfig('debug') > 1 ? '' : 'hidden'; ?>" id="advanced">
                    <div class="alert alert-info alert-dismissible" role="alert">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <p><?php eT('All fields below (except CSS framework name) must be either a valid JSON array or the string "inherit".'); ?></p>
                    </div>

                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <p><strong><?php eT('Warning!'); ?></strong> <?php eT("Don't touch the values below unless you know what you're doing."); ?></p>
                    </div>

                    <?php
                    $actionBaseUrl = 'themeOptions/update/';
                    $actionUrlArray = ['id' => $model->id];

                    if ($model->sid) {
                        unset($actionUrlArray['id']);
                        $actionUrlArray['sid'] = $model->sid;
                        $actionUrlArray['surveyd'] = $model->sid;
                        $actionUrlArray['gsid'] = $model->gsid;
                        $actionBaseUrl = 'themeOptions/updateSurvey/';
                    }
                    if ($model->gsid) {
                        unset($actionUrlArray['id']);
                        $actionBaseUrl = 'themeOptions/updateSurveyGroup/';
                        $actionUrlArray['gsid'] = $model->gsid;
                        $actionUrlArray['id'] = $model->id;
                    }

                    $actionUrl = Yii::app()->getController()->createUrl($actionBaseUrl, $actionUrlArray);
                    ?>
                    <div class="container-fluid">
                        <div class="row ls-space margin bottom-15">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6 h4">
                                        <?php printf(gT("Upload an image (maximum size: %d MB):"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?php echo TbHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'uploadimage', 'name' => 'uploadimage', 'enctype' => 'multipart/form-data']); ?>
                                        <span id="fileselector">
                                            <label class="btn btn-default col-xs-8" for="upload_image">
                                                <input class="hidden" id="upload_image" name="upload_image" type="file">
                                                <i class="fa fa-upload ls-space margin right-10"></i><?php eT("Upload"); ?>
                                            </label>
                                        </span>

                                        <input type='hidden' name='templatename' value='<?php echo $model->template_name; ?>'/>
                                        <input type='hidden' name='templateconfig' value='<?php echo $model->id; ?>'/>
                                        <input type='hidden' name='action' value='templateuploadimagefile'/>
                                        <?php echo TbHtml::endForm() ?>
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
                                <?php $form = $this->beginWidget(
                                    'TbActiveForm',
                                    [
                                        'id'                   => 'template-options-form',
                                        'enableAjaxValidation' => false,
                                        'htmlOptions'          => ['class' => 'form '],
                                        'action'               => $actionUrl
                                    ]
                                ); ?>
                                <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
                                <?php echo $form->errorSummary($model); ?>


                                <?php echo $form->hiddenField($model, 'template_name'); ?>
                                <?php echo $form->hiddenField($model, 'sid'); ?>
                                <?php echo $form->hiddenField($model, 'gsid'); ?>
                                <?php echo $form->hiddenField($model, 'uid'); ?>

                                <?php echo CHtml::hiddenField('optionInheritedValues', json_encode($optionInheritedValues)); ?>
                                <?php echo CHtml::hiddenField('optionCssFiles', json_encode($optionCssFiles)); ?>
                                <?php echo CHtml::hiddenField('optionCssFramework', json_encode($optionCssFramework)); ?>
                                <?php echo CHtml::hiddenField('translationInheritedValue', gT("Inherited value:") . ' '); ?>

                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'files_css'); ?>
                                        <?php echo $form->textArea($model, 'files_css', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'files_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'files_js'); ?>
                                        <?php echo $form->textArea($model, 'files_js', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'files_js'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'files_print_css'); ?>
                                        <?php echo $form->textArea($model, 'files_print_css', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'files_print_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'options'); ?>
                                        <?php echo $form->textArea($model, 'options', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'options'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'cssframework_name'); ?>
                                        <?php echo $form->textField($model, 'cssframework_name', ['size' => 45, 'maxlength' => 45]); ?>
                                        <?php echo $form->error($model, 'cssframework_name'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'cssframework_css'); ?>
                                        <?php echo $form->textArea($model, 'cssframework_css', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'cssframework_css'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'cssframework_js'); ?>
                                        <?php echo $form->textArea($model, 'cssframework_js', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'cssframework_js'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($model, 'packages_to_load'); ?>
                                        <?php echo $form->textArea($model, 'packages_to_load', ['rows' => 6, 'cols' => 50]); ?>
                                        <?php echo $form->error($model, 'packages_to_load'); ?>
                                    </div>
                                </div>
                                <div class="row buttons hidden">
                                    <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['class' => 'btn-success']); ?>
                                </div>

                                <?php $this->endWidget(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($model->sid)) : // If we are in survey view, we have an additional div that we need to close  ?>
</div>
    <?php endif; ?>

<!-- Form for image file upload -->
<div class="hidden">
    <?php echo TbHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'upload_frontend', 'name' => 'upload_frontend', 'enctype' => 'multipart/form-data']); ?>
    <?php if (isset($aTemplateConfiguration['sid']) && !empty($aTemplateConfiguration['sid'])) : ?>
        <input type='hidden' name='surveyid' value='<?= $aTemplateConfiguration['sid'] ?>'/>
    <?php endif; ?>
    <input type='hidden' name='templatename' value='<?php echo $aTemplateConfiguration['template_name']; ?>'/>
    <input type='hidden' name='templateconfig' value='<?php echo $aTemplateConfiguration['id']; ?>'/>
    <input type='hidden' name='action' value='templateuploadimagefile'/>
    <?php echo TbHtml::endForm() ?>
</div>

<?php
Yii::app()->getClientScript()->registerScript(
    "themeoptions-scripts",
    '

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
            $("#theme-options-tabs li a").click(function(e){
                if ($(this).attr("href") == "#advanced"){
                    $("#advanced").show();
                    $("#simple").hide();
                    $("[id^=category-]").hide();
                } else {
                    $("#advanced").hide();
                    $("#simple").show();
                    $("[id^=category-]").hide();
                    $($(this).attr("href")).show();
                }
            });
            

        });
    ',
    LSYii_ClientScript::POS_END
);
?>
