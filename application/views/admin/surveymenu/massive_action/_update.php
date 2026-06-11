<?php

/**
 * Render the selector for surveys massive actions.
 *
 */

?>
<?php
    $model = Surveymenu::model();
?>

<form class="custom-modal-datas form form-horizontal">
	<div class="container-fluid">
        <fieldset class="mb-0" aria-labelledby="massedit-modify-legend">
            <legend id="massedit-modify-legend" class="visually-hidden"><?php eT("Modify"); ?></legend>
            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <span id="massedit-modify-group-label"
                          class="form-label"><?php eT("Modify"); ?></span>
                </div>
                <div class="col-md-11"></div>
            </div>
            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <input type="checkbox" id="massedit_position_modify" class="action_check_to_keep_old_value" aria-labelledby="massedit-modify-group-label massedit_position_fieldlabel" />
                </div>
                <label id="massedit_position_fieldlabel" class="col-md-3 form-label" for="position"><?php eT("Position:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('position', 'lskeep', ['lskeep' => gT('Keep old value')] + $model->getPositionOptions(), ['id' => 'position', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField']); ?>
                </div>
            </div>

            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <input type="checkbox" id="massedit_parent_id_modify" class="action_check_to_keep_old_value" aria-labelledby="massedit-modify-group-label massedit_parent_id_fieldlabel" />
                </div>
                <label id="massedit_parent_id_fieldlabel" class="col-md-3 form-label" for="parent_id"><?php eT("Parent menu:"); ?></label>
                <div class="col-md-8">
                        <?php echo TbHtml::dropDownList('parent_id', 'lskeep', ['lskeep' => gT('Keep old value')] + $model->getMenuIdOptions(), ['id' => 'parent_id', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField']); ?>
                    </div>
                </div>


                <div class="ex-form-group mb-3">
                    <div class="col-md-1">
                        <input type="checkbox" id="massedit_survey_id_modify" class="action_check_to_keep_old_value" aria-labelledby="massedit-modify-group-label massedit_survey_id_fieldlabel" />
                    </div>
                    <label id="massedit_survey_id_fieldlabel" class="col-md-3 form-label" for="survey_id"><?php eT("Survey:"); ?></label>
                    <div class="col-md-8">
                        <?php echo TbHtml::dropDownList('survey_id', 'lskeep', ['lskeep' => gT('Keep old value')] + $model->getSurveyIdOptions(), ['id' => 'survey_id', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField']); ?>
                </div>
            </div>

            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <input type="checkbox" id="massedit_user_id_modify" class="action_check_to_keep_old_value" aria-labelledby="massedit-modify-group-label massedit_user_id_fieldlabel" />
                </div>
                <label id="massedit_user_id_fieldlabel" class="col-md-3 form-label" for="user_id"><?php eT("User:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('user_id', 'lskeep', ['lskeep' => gT('Keep old value')] + $model->getUserIdOptions(), ['id' => 'user_id', 'disabled' => 'disabled', 'class' => 'form-select custom-data selector_submitField']); ?>
                </div>
            </div>
        </fieldset>

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
