<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */
$animationOptions = '
        <optgroup label="Attention Seekers">
          <option value="bounce">bounce</option>
          <option value="flash">flash</option>
          <option value="pulse">pulse</option>
          <option value="rubberBand">rubberBand</option>
          <option value="shake">shake</option>
          <option value="swing">swing</option>
          <option value="tada">tada</option>
          <option value="wobble">wobble</option>
          <option value="jello">jello</option>
        </optgroup>

        <optgroup label="Bouncing Entrances">
          <option value="bounceIn">bounceIn</option>
          <option value="bounceInDown">bounceInDown</option>
          <option value="bounceInLeft">bounceInLeft</option>
          <option value="bounceInRight">bounceInRight</option>
          <option value="bounceInUp">bounceInUp</option>
        </optgroup>

        <optgroup label="Bouncing Exits">
          <option value="bounceOut">bounceOut</option>
          <option value="bounceOutDown">bounceOutDown</option>
          <option value="bounceOutLeft">bounceOutLeft</option>
          <option value="bounceOutRight">bounceOutRight</option>
          <option value="bounceOutUp">bounceOutUp</option>
        </optgroup>

        <optgroup label="Fading Entrances">
          <option value="fadeIn">fadeIn</option>
          <option value="fadeInDown">fadeInDown</option>
          <option value="fadeInDownBig">fadeInDownBig</option>
          <option value="fadeInLeft">fadeInLeft</option>
          <option value="fadeInLeftBig">fadeInLeftBig</option>
          <option value="fadeInRight">fadeInRight</option>
          <option value="fadeInRightBig">fadeInRightBig</option>
          <option value="fadeInUp">fadeInUp</option>
          <option value="fadeInUpBig">fadeInUpBig</option>
        </optgroup>

        <optgroup label="Fading Exits">
          <option value="fadeOut">fadeOut</option>
          <option value="fadeOutDown">fadeOutDown</option>
          <option value="fadeOutDownBig">fadeOutDownBig</option>
          <option value="fadeOutLeft">fadeOutLeft</option>
          <option value="fadeOutLeftBig">fadeOutLeftBig</option>
          <option value="fadeOutRight">fadeOutRight</option>
          <option value="fadeOutRightBig">fadeOutRightBig</option>
          <option value="fadeOutUp">fadeOutUp</option>
          <option value="fadeOutUpBig">fadeOutUpBig</option>
        </optgroup>

        <optgroup label="Flippers">
          <option value="flip">flip</option>
          <option value="flipInX">flipInX</option>
          <option value="flipInY">flipInY</option>
          <option value="flipOutX">flipOutX</option>
          <option value="flipOutY">flipOutY</option>
        </optgroup>

        <optgroup label="Lightspeed">
          <option value="lightSpeedIn">lightSpeedIn</option>
          <option value="lightSpeedOut">lightSpeedOut</option>
        </optgroup>

        <optgroup label="Rotating Entrances">
          <option value="rotateIn">rotateIn</option>
          <option value="rotateInDownLeft">rotateInDownLeft</option>
          <option value="rotateInDownRight">rotateInDownRight</option>
          <option value="rotateInUpLeft">rotateInUpLeft</option>
          <option value="rotateInUpRight">rotateInUpRight</option>
        </optgroup>

        <optgroup label="Rotating Exits">
          <option value="rotateOut">rotateOut</option>
          <option value="rotateOutDownLeft">rotateOutDownLeft</option>
          <option value="rotateOutDownRight">rotateOutDownRight</option>
          <option value="rotateOutUpLeft">rotateOutUpLeft</option>
          <option value="rotateOutUpRight">rotateOutUpRight</option>
        </optgroup>

        <optgroup label="Sliding Entrances">
          <option value="slideInUp">slideInUp</option>
          <option value="slideInDown">slideInDown</option>
          <option value="slideInLeft">slideInLeft</option>
          <option value="slideInRight">slideInRight</option>

        </optgroup>
        <optgroup label="Sliding Exits">
          <option value="slideOutUp">slideOutUp</option>
          <option value="slideOutDown">slideOutDown</option>
          <option value="slideOutLeft">slideOutLeft</option>
          <option value="slideOutRight">slideOutRight</option>
          
        </optgroup>
        
        <optgroup label="Zoom Entrances">
          <option value="zoomIn">zoomIn</option>
          <option value="zoomInDown">zoomInDown</option>
          <option value="zoomInLeft">zoomInLeft</option>
          <option value="zoomInRight">zoomInRight</option>
          <option value="zoomInUp">zoomInUp</option>
        </optgroup>
        
        <optgroup label="Zoom Exits">
          <option value="zoomOut">zoomOut</option>
          <option value="zoomOutDown">zoomOutDown</option>
          <option value="zoomOutLeft">zoomOutLeft</option>
          <option value="zoomOutRight">zoomOutRight</option>
          <option value="zoomOutUp">zoomOutUp</option>
        </optgroup>

        <optgroup label="Specials">
          <option value="hinge">hinge</option>
          <option value="jackInTheBox">jackInTheBox</option>
          <option value="rollIn">rollIn</option>
          <option value="rollOut">rollOut</option>
        </optgroup>
      ';
      $bootswatchOption ='
        <option value="">Basic Bootstrap</option>
        <option value="css/cerulean.css">Cerulean</option>
        <option value="css/cosmo.css">Cosmo</option>
        <option value="css/cyborg.css">Cyborg</option>
        <option value="css/darkly.css">Darkly</option>
        <option value="css/flatly.css">Flatly</option>
        <option value="css/journal.css">Journal</option>
        <option value="css/lumen.css">Lumen</option>
        <option value="css/paper.css">Paper</option>
        <option value="css/readable.css">Readable</option>
        <option value="css/sandstone.css">Sandstone</option>
        <option value="css/simplex.css">Simplex</option>
        <option value="css/slate.css">Slate</option>
        <option value="css/solar.css">Solar</option>
        <option value="css/spacelab.css">Spacelab</option>
        <option value="css/superhero.css">Superhero</option>
        <option value="css/united.css">United</option>
        <option value="css/yeti.css">Yeti</option>
      ';
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

                        $optionForm = "
                        <form class='form form-horizontal action_update_options_string_form' action=''>
                            <div class='row'>
                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_ajaxmode' class='col-sm-6 control-label'>Ajaxmode</label>
                                        <div class='col-sm-6'>
                                        <input name='ajaxmode' type='checkbox' class='form-control selector_option_value_field action_activate_bootstrapswitch' id='simple_edit_options_ajaxmode'/>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_brandlogo' class='col-sm-6 control-label'>Brandlogo</label>
                                        <div class='col-sm-6'>
                                        <input type='checkbox' name='brandlogo' class='form-control selector_option_value_field action_activate_bootstrapswitch' id='simple_edit_options_brandlogo' />
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_backgroundimage' class='col-sm-6 control-label'>Background image</label>
                                        <div class='col-sm-6'>
                                        <input type='checkbox' name='backgroundimage' class='form-control simple_edit_options_backgroundimage action_activate_bootstrapswitch' id='backgroundimage' />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <hr/>
                            </div>
                            <div class='row'>
                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_animatebody' class='col-sm-6 control-label'>Animate body</label>
                                        <div class='col-sm-6'>
                                        <input type='checkbox' class='form-control selector_option_value_field action_activate_bootstrapswitch' id='simple_edit_options_animatebody' name='animatebody' />
                                        </div>
                                    </div>
                                </div>
                                <div class='col-sm-12 col-md-4'>
                                </div>
                                <div class='col-sm-12 col-md-8'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_bodyanimation' class='col-sm-2 control-label'>Body animation</label>
                                        <div class='col-sm-10'>
                                            <select class='form-control selector_option_value_field' id='simple_edit_options_bodyanimation' name='bodyanimation'>
                                                ".$animationOptions."
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_animatequestion' class='col-sm-6 control-label'>Animate question</label>
                                        <div class='col-sm-6'>
                                        <input type='checkbox' class='form-control selector_option_value_field action_activate_bootstrapswitch' id='simple_edit_options_animatequestion' name='animatequestion' />
                                        </div>
                                    </div>
                                </div>
                                <div class='col-sm-12 col-md-8'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_questionanimation' class='col-sm-2 control-label'>Question animation</label>
                                        <div class='col-sm-10'>
                                            <select class='form-control selector_option_value_field' id='simple_edit_options_questionanimation' name='questionanimation'>
                                                ".$animationOptions."
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-sm-12 col-md-4'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_animatealert' class='col-sm-6 control-label'>Animate alert</label>
                                        <div class='col-sm-6'>
                                        <input type='checkbox' class='form-control selector_option_value_field action_activate_bootstrapswitch' id='simple_edit_options_animatealert' name='animatealert' />
                                        </div>
                                    </div>
                                </div>
                                <div class='col-sm-12 col-md-8'>
                                    <div class='form-group'>
                                        <label for='simple_edit_options_alertanimation' class='col-sm-2 control-label'>Alert animation</label>
                                        <div class='col-sm-10'>
                                            <select class='form-control selector_option_value_field' id='simple_edit_options_alertanimation' name='alertanimation'>
                                                ".$animationOptions."
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='row ls-space margin top-15 bottom-15'>
                                <hr/>
                            </div>
                            <div class='row'>
                                <div class='col-sm-12'>
                                <div class='panel panel-default'>
                                    <div class='panel-heading'>Bootstrap theme</div>
                                    <div class='panel-body'>
                                        <div class='form-group'>
                                            <label for='simple_edit_cssframework' class='col-sm-2 control-label'>Bootswatch theme</label>
                                            <div class='col-sm-10'>
                                                <select class='form-control selector_cssframework_value_field' id='simple_edit_cssframework' name='cssframework'>
                                                    ".$bootswatchOption."
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <button class='btn btn-success col-md-2 col-sm-4 col-xs-6 action_update_options_string_button'>".gT('Save')."</button>
                                </div>
                            </div>
                        </form>
                        ";
                        echo $optionForm;
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

<script type="text/javascript">
$(document).on('ready pjax:complete',function(){
    //activate the bootstrap switch for checkboxes
    $('.action_activate_bootstrapswitch').bootstrapSwitch();
    //get option Object from Template configuration options
    var optionObject = {}
    try{
        optionObject = JSON.parse($('#TemplateConfiguration_options').val());
    } catch(e){ console.error('No valid option field!'); }

    //check if a form exists to parse the simple option
    if($('.action_update_options_string_form').length > 0){
        //Update values in the form to the template options
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            
            var itemValue = optionObject[$(item).attr('name')];
            $(item).val(itemValue);
            //if it is a checkbox, check it and propagate the change to bootstrapSwitch
            if($(item).attr('type') == 'checkbox' && itemValue !='off') $(item).prop('checked', true).trigger('change');
        });

        //if the save button is clicked write everything into the template option field and send the form
        $('.action_update_options_string_button').on('click', function(evt){
            evt.preventDefault();
            var newOptionObject = {};
            //get all values
            $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
                newOptionObject[$(item).attr('name')] = $(item).val();
                //again extra check for checkboxes
                if($(item).attr('type') == 'checkbox'){
                    newOptionObject[$(item).attr('name')] = $(item).prop('checked') ? 'on' : 'off';
                }
            });
            //now write the newly created object to the correspondent field as a json string
            $('#TemplateConfiguration_options').val(JSON.stringify(newOptionObject));
            //and submit the form
            $('#template-options-form').find('button[type=submit]').trigger('click');
        });

        //hotswapping the fields
        $('.action_update_options_string_form').find('.selector_option_value_field').on('change switchChange.bootstrapSwitch', function(evt){
            optionObject[$(this).attr('name')] = $(this).val(); 
            if($(this).attr('type') == 'checkbox'){
                optionObject[$(this).attr('name')] = $(this).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });

        //Bootstrap theming?
        if($('#simple_edit_cssframework').length>0){
            var currentThemeObject = {};
            try{
                currentThemeObject = JSON.parse($('#TemplateConfiguration_cssframework_css').val());
            } catch(e){ console.error('No valid css framework theme field!'); }
            currentThemeObject.replace = currentThemeObject.replace || [['css/bootstrap.css','']];

            $('#simple_edit_cssframework').val(currentThemeObject.replace[0][1]);

            $('#simple_edit_cssframework').on('change', function(evt){
                //{"replace": [["css/bootstrap.css","css/flatly.css"]]}
                currentThemeObject.replace = currentThemeObject.replace || [[]];
                currentThemeObject.replace[0][1] = $('#simple_edit_cssframework').val();

                $('#TemplateConfiguration_cssframework_css').val(JSON.stringify(currentThemeObject));
            })
        }   
    }
});
</script>