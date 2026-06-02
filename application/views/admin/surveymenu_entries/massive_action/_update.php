<?php

/**
 * Render the selector for surveys massive actions.
 *
 */

?>
<?php
    $model = SurveymenuEntries::model();
?>

<form class="custom-modal-datas form form-horizontal">
    <div class="container-fluid">
        <fieldset class="mb-0" aria-labelledby="massedit-entry-modify-legend">
            <legend id="massedit-entry-modify-legend" class="visually-hidden"><?php eT("Modify"); ?></legend>
        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <span id="massedit-entry-modify-group-label" class="form-label"><?php eT("Modify"); ?></span>
            </div>
            <div class="col-md-11"></div>
        </div>
        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <input
                    type="checkbox"
                    id="massedit_entry_menu_id_modify"
                    class="action_check_to_keep_old_value"
                    aria-labelledby="massedit-entry-modify-legend massedit_entry_menu_id_fieldlabel"
                />
            </div>
            <label id="massedit_entry_menu_id_fieldlabel" class="col-md-3 form-label" for="menu_id"><?php eT("Menu ID:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::dropDownList('menu_id', 'lskeep', (['lskeep' => gT('Keep old value')] + $model->getMenuIdOptions()), ['id' => 'menu_id', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField', 'aria-label' => gT('Menu ID:')]);?>
            </div>
        </div>

        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <input
                    type="checkbox"
                    id="massedit_entry_menu_class_modify"
                    class="action_check_to_keep_old_value"
                    aria-labelledby="massedit-entry-modify-legend massedit_entry_menu_class_fieldlabel"
                />
            </div>
            <label id="massedit_entry_menu_class_fieldlabel" class="col-md-3 form-label" for="menu_class"><?php eT("Menu class:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('menu_class', 'lskeep', array('size' => 60,'maxlength' => 255,'id' => 'menu_class', 'disabled' => 'disabled','class' => 'custom-data selector_submitField', 'aria-label' => gT('Menu class:')));?>
            </div>
        </div>


        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <input
                    type="checkbox"
                    id="massedit_entry_permission_modify"
                    class="action_check_to_keep_old_value"
                    aria-labelledby="massedit-entry-modify-legend massedit_entry_permission_fieldlabel"
                />
            </div>
            <label id="massedit_entry_permission_fieldlabel" class="col-md-3 form-label" for="permission"><?php eT("Permission:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('permission', 'lskeep', array('size' => 60,'maxlength' => 255,'id' => 'permission', 'disabled' => 'disabled','class' => 'custom-data selector_submitField', 'aria-label' => gT('Permission:')));?>
            </div>
        </div>

        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <input
                    type="checkbox"
                    id="massedit_entry_permission_grade_modify"
                    class="action_check_to_keep_old_value"
                    aria-labelledby="massedit-entry-modify-legend massedit_entry_permission_grade_fieldlabel"
                />
            </div>
            <label id="massedit_entry_permission_grade_fieldlabel" class="col-md-3 form-label" for="permission_grade"><?php eT("Permission level:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('permission_grade', 'lskeep', array('size' => 60,'maxlength' => 255,'id' => 'permission_grade', 'disabled' => 'disabled','class' => 'custom-data selector_submitField', 'aria-label' => gT('Permission level:')));?>
            </div>
        </div>
        </fieldset>

        <div class="row ls-space margin bottom-10">
            <button class="btn btn-warning float-end" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedOptionsMassEdit" aria-expanded="false" aria-controls="collapseAdvancedOptionsMassEdit">
                <?php eT('Toggle advanced options') ?>
            </button>
        </div>
        <!-- Start collapsed advanced options -->
        <div class="collapse" id="collapseAdvancedOptionsMassEdit">
            <fieldset class="mb-0" aria-labelledby="massedit-entry-advanced-legend">
                <legend id="massedit-entry-advanced-legend" class="visually-hidden"><?php eT('Advanced options'); ?></legend>

            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <input
                        type="checkbox"
                        id="massedit_entry_user_id_modify"
                        class="action_check_to_keep_old_value"
                        aria-labelledby="massedit-entry-advanced-legend massedit_entry_user_id_fieldlabel"
                    />
                </div>
                <label id="massedit_entry_user_id_fieldlabel" class="col-md-3 form-label" for="user_id"><?php eT("User:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('user_id', 'lskeep', array_merge(['lskeep' => gT('Keep old value')], $model->getUserIdOptions()), ['id' => 'user_id', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField', 'aria-label' => gT('User:')]);?>
                </div>
            </div>
            
            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <input
                        type="checkbox"
                        id="massedit_entry_language_modify"
                        class="action_check_to_keep_old_value"
                        aria-labelledby="massedit-entry-advanced-legend massedit_entry_language_fieldlabel"
                    />
                </div>
                <label id="massedit_entry_language_fieldlabel" class="col-md-3 form-label" for="language"><?php eT("Language:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::textField('language', 'lskeep', array('size' => 60,'maxlength' => 255,'id' => 'language', 'disabled' => 'disabled', 'class' => 'custom-data selector_submitField', 'aria-label' => gT('Language:')));?>
                </div>
            </div>
            </fieldset>
        
        </div>

        <?php echo TbHtml::hiddenField('changed_by', Yii::app()->user->id, ['class' => 'custom-data']);?>
        <?php echo TbHtml::hiddenField('changed_at', date('Y-m-d H:i:s'), ['class' => 'custom-data']);?>
        
    </div>
</form>
<!-- form -->
<script>
$('.action_check_to_keep_old_value').on('click', function(){
        var currentValue = !$(this).prop('checked');
        var myFormGroup = $(this).closest('.ex-form-group');

        myFormGroup.find('input:not(.action_check_to_keep_old_value)').prop('disabled', currentValue)
        myFormGroup.find('select').prop('disabled', currentValue)

        if(currentValue){
            myFormGroup.find('.selector_submitField').val('lskeep');
        } else {
            myFormGroup.find('input.selector_submitField').val('');
        }

    });
</script>
