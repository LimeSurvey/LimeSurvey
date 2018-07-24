<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php if ($editType == 'new') : eT('Add attribute'); else: eT('Edit attribute'); endif; ?></h4>
</div>
<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'editAttributeNameActiveForm',
            'action' => array('admin/participants/sa/editAttributeName'),
            'htmlOptions' => array('class' => ''), // for inset effect
        )
    );
?>
    <input type="hidden" name="oper" value="<?php echo $editType; ?>" />
    <?php 
    if($editType=='edit')
    {
        echo '<input type="hidden" id="ParticipantAttributeName_attribute_id" name="ParticipantAttributeName[attribute_id]" value="'.$model->attribute_id.'" />';
    }
    echo "<legend>".gT("Basic settings")."</legend>";
        $baseControlGroupHtmlOptions = array(
             'groupOptions'=> array('class'=>''),
             'labelOptions'=> array('class'=> ''),
             'class' => '',
             'required' => 'required'
        );
        echo $form->textFieldControlGroup($model,'defaultname', $baseControlGroupHtmlOptions);
        echo $form->dropDownListControlGroup($model,'attribute_type', $model->attributeTypeDropdownArray, $baseControlGroupHtmlOptions);
        echo 
        "<div class='row'>
            <label class='control-label col-sm-12'>".gT("Should this attribute be visible on the panel?")."</label>
            <div class='col-sm-12'>
                &nbsp;
                <label class='radio-inline'>"
                 . "<input name=\"ParticipantAttributeName[visible]\" id=\"ParticipantAttributeName_visible\" type=\"radio\" value=\"TRUE\" "
                    .($model->visible == "TRUE" ? "checked" : "")." />"
                 . gT("Yes")."
                </label>
                <label class='radio-inline'>"
                 . "<input name=\"ParticipantAttributeName[visible]\" id=\"ParticipantAttributeName_visible\" type=\"radio\" value=\"FALSE\" "
                    .($model->visible == "FALSE" ? "checked" : "")." />"
                 . gT("No")."
                </label>
            </div>
        </div>
        <br/>"; 
    ?>
    <div id="ParticipantAttributeNamesDropdownEdit" class="row form-group" style="display: none;">
        <div class="row">
            <div class="col-xs-2">
                <button class="btn btn-default btn-block" id="addDropdownField" data-toggle="tooltip" title="<?php eT('Add dropdown field'); ?>"><i class="fa fa-plus-circle text-success"></i></button>
            </div>
            <h4 class="col-xs-8 col-offset-xs-2"><?php eT("Dropdown fields") ?></h4>
        </div>
        <div id='ParticipantAttributeNamesDropdownEditList'>
            <?php 
                foreach($model->getAttributesValues($model->attribute_id) as $attribute_value){
                    echo "<div class='control-group'>";
                    echo "<div class='dropDownContainer col-xs-8 col-offset-xs-2'>";
                    echo "<input class='form-control' name='ParticipantAttributeNamesDropdown[]' value='".$attribute_value['value']."' />";
                    echo "</div>";
                    echo '<div class="col-xs-1">
                            <button class="btn btn-default form-group action_delDropdownField">
                                <i class="fa fa-trash text-danger"></i>
                            </button>
                        </div>
                    </div>';
                }
            ?>
            <div class='control-group'>
                <div class='dropDownContainer col-xs-8 col-offset-xs-2'>
                    <input class='form-control' name='ParticipantAttributeNamesDropdown[]' value='' />
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-default form-group action_delDropdownField">
                        <i class="fa fa-trash text-danger"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
     <legend><?php eT("Languages") ?></legend>
        <div class="row form-group">
            <label class=" col-xs-12 control-label" for="ParticipantAttributeName_addLanguage_language"><?php eT("Add language");?></label>
            <div>
                <div class=" col-xs-11">
                <?php
                    echo TbHtml::dropDownList("ParticipantAttributeName_addLanguage_language", '', $languagesForDropdown,array('encode' => false));
                ?>
                </div>
            </div>
            <div class="col-xs-1">
                <button class="btn btn-default form-group" id="addLanguageField" data-toggle="tooltip" title="<?php eT("Add a new language") ?>">
                    <i class="fa fa-plus-circle text-success"></i>
                </button>
            </div>
        </div>
        <div id='languagesList' class="row">
            <?php 
                if($editType!=='edit'){
                    $languageKey = Yii::app()->getLanguage();
                    echo 
                    '<div class="form-group" data-lang="'.$languageKey .'">
                        <label class="col-sm-12 control-label" for="ParticipantAttributeNameLanguages_'.$languageKey.'">'.getLanguageNameFromCode($languageKey,false).'</label>
                        <div>
                            <div class=" col-xs-11">
                                <input required class="form-control" name="ParticipantAttributeNameLanguages['.$languageKey.']" id="ParticipantAttributeNameLanguages_'.$languageKey.'" type="text" value="">
                            </div>
                            <div class="col-xs-1">
                                <button class="btn btn-default form-group action_delLanguageField">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
                }
                foreach($languagesOfAttribute as $languageKey => $languageOfAttribute)
                {
                    echo 
                    '<div class="col-sm-12 form-group" data-lang="'.$languageKey.'">
                        <label class=" control-label" for="ParticipantAttributeNameLanguages_'.$languageKey.'">'.getLanguageNameFromCode($languageKey,false).'</label>
                        <div>
                            <div class=" col-xs-11">
                                <input class="form-control" name="ParticipantAttributeNameLanguages['.$languageKey.']" id="ParticipantAttributeNameLanguages_'.$languageKey.'" type="text" value="'.$languageOfAttribute.'">
                            </div>
                            <div class="col-xs-1">
                                <button class="btn btn-default form-group action_delLanguageField">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
                }
            ?>
            <div class="hidden">
                <div class=" form-group" id="dummyLanguageInputGroup">
                        <label class=" control-label selector_languageAddLabel" for="dummyNameForInputLabel"></label>
                        <div>
                            <div class="">
                                <input class="form-control selector_languageAddInput" name="dummyParticipantAttributeNameLanguages" type="text" value="">
                            </div>
                        </div>
                    </div>
                <div class='control-group'  id="dummyDropdownInputGroup">
                    <div class='dropDownContainer col-xs-8 col-offset-xs-2'>
                        <input class='form-control' name='dummyParticipantAttributeNamesDropdown' value='' />
                    </div>
                    <div class="col-xs-1">
                        <button class="btn btn-default form-group action_delDropdownField">
                            <i class="fa fa-trash text-danger"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <script>
        jQuery('#ParticipantAttributeName_attribute_type').on('change', function(){
            console.log($(this).val() == "DD");
            if($(this).val() == "DD"){
                $('#ParticipantAttributeNamesDropdownEdit').slideDown();
            } else {
                $('#ParticipantAttributeNamesDropdownEdit').slideUp();
            }
        })
        jQuery('.action_delDropdownField').on('click', function(e){
            e.preventDefault();
            $(this).closest('div.control-group').remove();
            return false;
        });
        jQuery('#addDropdownField').on('click', function(e){
            e.preventDefault();
            jQuery('#dummyDropdownInputGroup')
            .clone()
            .appendTo($('#ParticipantAttributeNamesDropdownEditList'))
            .removeAttr('id')
            .find('input')
                .attr('name', 'ParticipantAttributeNamesDropdown[]');
            return false;
        });
        jQuery('#addLanguageField').on('click', function(e){
            e.preventDefault();
            var langKey = $('#ParticipantAttributeName_addLanguage_language').val();
            if(langKey !== ''){
                jQuery('#dummyLanguageInputGroup')
                .clone()
                .appendTo($('#languagesList'))
                .data('lang',langKey)
                .find('input.selector_languageAddInput')
                    .attr('name', 'ParticipantAttributeNameLanguages['+langKey+']')
                    .attr('id', 'ParticipantAttributeNameLanguages_'+langKey)
                .end()
                .find('label.selector_languageAddLabel')
                    .attr('for', 'ParticipantAttributeNameLanguages_'+langKey)
                    .html($('#ParticipantAttributeName_addLanguage_language option:selected').text());
            }
            return false;
        });
        jQuery('.action_delLanguageField').on('click', function(e){
            e.preventDefault();
            var self = this,
                attribute_id = $('#ParticipantAttributeName_attribute_id').val(),
                formGroup = $(this).closest('div.form-group.'),
                lang = formGroup.data('lang');
                $.ajax({
                    url: deleteLanguageFromAttributeUrl,
                    data: {attribute_id: attribute_id, lang: lang},
                    method: 'POST',
                    dataType: "json",
                    success: function(result){
                        if(result.success){
                            formGroup.fadeOut(400, function(){formGroup.remove()});
                        } else {
                            window.LS.notifyFader(result.errorMessage, 'well-lg bg-danger text-center');
                        }
                    }
                })
            
            return false;
        });
        jQuery(function(){jQuery('#ParticipantAttributeName_attribute_type').trigger('change');});
    </script>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_editAttributeName"><?php eT("Save")?></button>
</div>
<?php
$this->endWidget();
?>
