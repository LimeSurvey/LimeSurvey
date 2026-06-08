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
        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                <?php eT("Modify"); ?>
                </label>
            </div>
            <div class="col-md-11"></div>
        </div>
        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value"></input>
                </label>
            </div>
            <label class="col-md-3 form-label"  for='menu_id'><?php eT("Menu ID:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::dropDownList('menu_id', 'lskeep', (['lskeep' => gT('Keep old value')] + $model->getMenuIdOptions()), ['disabled' => 'disabled','class' => 'form-select custom-data selector_submitField']);?>
            </div>
        </div>

        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value"></input>
                </label>
            </div>
            <label class="col-md-3 form-label"  for='menu_class'><?php eT("Menu class:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('menu_class', 'lskeep', array('size' => 60,'maxlength' => 255,'disabled' => 'disabled','class' => 'custom-data selector_submitField'));?>
            </div>
        </div>


        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value"></input>
                </label>
            </div>
            <label class="col-md-3 form-label"  for='permission'><?php eT("Permission:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('permission', 'lskeep', array('size' => 60,'maxlength' => 255,'disabled' => 'disabled','class' => 'custom-data selector_submitField'));?>
            </div>
        </div>

        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value"></input>
                </label>
            </div>
            <label class="col-md-3 form-label"  for='permission_grade'><?php eT("Permission level:"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::textField('permission_grade', 'lskeep', array('size' => 60,'maxlength' => 255,'disabled' => 'disabled','class' => 'custom-data selector_submitField'));?>
            </div>
        </div>

        <div class="row ls-space margin bottom-10">
            <button class="btn btn-warning float-end" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedOptionsMassEdit">
                <?php eT('Toggle advanced options') ?>
            </button>
        </div>
        <!-- Start collapsed advanced options -->
        <div class="collapse" id="collapseAdvancedOptionsMassEdit">

            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <label class="" >
                        <input type="checkbox" class="action_check_to_keep_old_value"></input>
                    </label>
                </div>
                <label class="col-md-3 form-label"  for='permission_grade'><?php eT("User:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('user_id', 'lskeep', array_merge(['lskeep' => gT('Keep old value')], $model->getUserIdOptions()), ['disabled' => 'disabled','class' => 'form-select custom-data selector_submitField']);?>
                </div>
            </div>
            
            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <label class="" >
                        <input type="checkbox" class="action_check_to_keep_old_value"></input>
                    </label>
                </div>
                <label class="col-md-3 form-label"  for='language'><?php eT("Language:"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::textField('language', 'lskeep', array('size' => 60,'maxlength' => 255,'disabled' => 'disabled', 'class' => 'custom-data selector_submitField'));?>
                </div>
            </div>
        
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
