<div class="modal-header">
    <h5 class="modal-title" id="participant_edit_modal"><?php if ($editType == 'new') : eT('Add attribute'); else: eT('Edit attribute'); endif; ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
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
             'required' => 'required'
        );
        echo $form->textFieldControlGroup($model,'defaultname', $baseControlGroupHtmlOptions);
        echo $form->dropDownListControlGroup($model,'attribute_type', $model->attributeTypeDropdownArray, array_merge($baseControlGroupHtmlOptions, ['class' => 'form-select']));
    ?>
    <div class="ex-form-group mb-3" id="">
        <label class='form-label'><?php eT("Should this attribute be visible on the panel?"); ?></label>
        <div>
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => "ParticipantAttributeName[visible]",
                'checkedOption' => $model['visible'] === 'TRUE' ? '1' : '0',
                'selectOptions' => [
                    '1' => gT('On'),
                    '0' => gT('Off'),
                ],
            ]); ?>
        </div>
    </div>
    <div class="ex-form-group mb-3" id="">
        <label class=" form-label selector_languageAddLabel" for="dummyNameForInputLabel" title="<?php !$bEncrypted ? eT("Encryption is disabled because Sodium library isn't installed") : ''; ?>"><?php eT('Encrypted'); ?></label>
        <div>
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => "ParticipantAttributeName[encrypted]",
                'id'            => "encrypted",
                'checkedOption' => $model['encrypted'] === 'Y' ? '1' : '0',
                'selectOptions' => [
                    '1' => gT('On'),
                    '0' => gT('Off'),
                ],
                'htmlOptions'   => [
                    'disabled' => !$bEncrypted,
                ]
            ]); ?>
        </div>
    </div>
    <div id="ParticipantAttributeNamesDropdownEdit" class="row ex-form-group mb-3" style="display: none;">
        <div class="row">
            <div class="col-2">
                <button class="btn btn-outline-secondary btn-block" id="addDropdownField" data-bs-toggle="tooltip" title="<?php eT('Add dropdown field'); ?>"><i class="ri-add-circle-fill text-success"></i></button>
            </div>
            <h4 class="col-8 col-offset-2"><?php eT("Dropdown fields") ?></h4>
        </div>
        <div id='ParticipantAttributeNamesDropdownEditList'>
            <?php 
                foreach($model->getAttributesValues($model->attribute_id) as $attribute_value){
                    echo "<div class='control-group'>";
                    echo "<div class='dropDownContainer col-8 col-offset-2'>";
                    echo TbHtml::textField('ParticipantAttributeNamesDropdown[]', $attribute_value['value'], [
                        'class' => 'form-control',
                        'id' => ''
                    ]);
                    echo "</div>";
                    echo '<div class="col-1">
                            <button class="btn btn-outline-secondary ex-form-group mb-3 action_delDropdownField">
                                <i class="ri-delete-bin-fill text-danger"></i>
                            </button>
                        </div>
                    </div>';
                }
            ?>
            <div class='control-group'>
                <div class='dropDownContainer col-8 col-offset-2'>
                    <input class='form-control' name='ParticipantAttributeNamesDropdown[]' value='' />
                </div>
                <div class="col-1">
                    <button class="btn btn-outline-secondary ex-form-group mb-3 action_delDropdownField">
                        <i class="ri-delete-bin-fill text-danger"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
     <legend><?php eT("Languages") ?></legend>
        <div class="col-12 ex-form-group mb-3">
            <label class=" col-12 form-label" for="ParticipantAttributeName_addLanguage_language"><?php eT("Add language");?></label>
            <div class="d-flex flex-row align-items-center flex-wrap">
                <div class=" col-11">
                <?php
                    echo TbHtml::dropDownList("ParticipantAttributeName_addLanguage_language", '', $languagesForDropdown,array('encode' => false, 'class' => 'form-select'));
                ?>
                </div>
                <div class="col-1">
                    <button class="btn btn-outline-secondary ex-form-group ms-2" id="addLanguageField" data-bs-toggle="tooltip" title="<?php eT("Add a new language") ?>">
                        <i class="ri-add-circle-fill text-success"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id='languagesList' class="row">
            <?php 
                if($editType!=='edit'){
                    $languageKey = Yii::app()->getLanguage();
                    echo 
                    '<div class="ex-form-group mb-3" data-lang="'.$languageKey .'">
                        <label class="col-12 form-label" for="ParticipantAttributeNameLanguages_'.$languageKey.'">'.getLanguageNameFromCode($languageKey,false).'</label>
                        <div class="d-flex flex-row align-items-center flex-wrap">
                            <div class=" col-10">
                                <input required class="form-control" name="ParticipantAttributeNameLanguages['.$languageKey.']" id="ParticipantAttributeNameLanguages_'.$languageKey.'" type="text" value="">
                            </div>
                            <div class="col-1">
                                <button class="btn btn-outline-secondary ex-form-group ms-2 action_delLanguageField">
                                    <i class="ri-delete-bin-fill text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
                }
                foreach($languagesOfAttribute as $languageKey => $languageOfAttribute)
                {
                    echo 
                    '<div class="col-12 ex-form-group mb-3" data-lang="'.$languageKey.'">
                        <label class=" form-label" for="ParticipantAttributeNameLanguages_'.$languageKey.'">'.getLanguageNameFromCode($languageKey,false).'</label>
                        <div class="d-flex flex-row align-items-center flex-wrap">
                            <div class=" col-11">
                                <input class="form-control" name="ParticipantAttributeNameLanguages['.$languageKey.']" id="ParticipantAttributeNameLanguages_'.$languageKey.'" type="text" value="'.CHtml::encode($languageOfAttribute).'">
                            </div>
                            <div class="col-1">
                                <button class="btn btn-outline-secondary ex-form-group ms-2 action_delLanguageField">
                                    <i class="ri-delete-bin-fill text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
                }
            ?>
            <div class="d-none">
                <div class="ex-form-group mb-3" id="dummyLanguageInputGroup">
                        <label class=" form-label selector_languageAddLabel" for="dummyNameForInputLabel"></label>
                        <div>
                            <div class="">
                                <input class="form-control selector_languageAddInput" name="dummyParticipantAttributeNameLanguages" type="text" value="">
                            </div>
                        </div>
                    </div>
                <div class='control-group'  id="dummyDropdownInputGroup">
                    <div class='dropDownContainer col-8 col-offset-2'>
                        <input class='form-control' name='dummyParticipantAttributeNamesDropdown' value='' />
                    </div>
                    <div class="col-1">
                        <button class="btn btn-outline-secondary ex-form-group mb-3 action_delDropdownField">
                            <i class="ri-delete-bin-fill text-danger"></i>
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
                formGroup = $(this).closest('div.ex-form-group.'),
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
                            window.LS.ajaxAlerts(result.errorMessage, 'danger');
                        }
                    }
                })
            
            return false;
        });
        jQuery(function(){jQuery('#ParticipantAttributeName_attribute_type').trigger('change');});


    </script>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
    <button role="button" class="btn btn-primary action_save_modal_editAttributeName">
        <?php eT("Save")?>
    </button>
</div>
<?php
$this->endWidget();
?>