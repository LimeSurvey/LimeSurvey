<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<?php
    $model = Participant::model();
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
        <?php 
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')
            || $this->user_id == Yii::app()->session['loginID']
            || (Permission::model()->hasGlobalPermission('users', 'update')
                && $this->parent_id == Yii::app()->session['loginID']
            )
        ) {
        ?>
            <div class="ex-form-group mb-3">
                <div class="col-md-1">
                    <label class="" >
                        <input type="checkbox" class="action_check_to_keep_old_value" />
                    </label>
                </div>
                <label class="col-md-3 form-label"  for='owner_uid'><?php eT("Owner?"); ?></label>
                <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('owner_uid', 'lskeep', ['lskeep' => gT('Keep old value')] + $model->getOwnerOptions(), ['disabled'=>'disabled','class'=>'form-select custom-data selector_submitField'] );?>
                </div>
            </div>
        <?php } ?>
        <div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value" />
                </label>
            </div>
            <label class="col-md-3 form-label"  for='language'><?php eT("Language?"); ?></label>
            <div class="col-md-8">
                <?php echo TbHtml::dropDownList('language', 'lskeep', array_merge(['lskeep' => gT('Keep old value')], $model->getLanguageOptions()), ['disabled'=>'disabled','class'=>'form-select custom-data selector_submitField'] );?>
            </div>
        </div>
        
		<div class="ex-form-group mb-3">
            <div class="col-md-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value" />
                </label>
            </div>
            <label class="col-md-3 form-label"  for='blacklisted'><?php eT("Blocklisted?"); ?></label>
            <div class="col-md-8">
                    <?php echo TbHtml::dropDownList('blacklisted', 'lskeep', ['lskeep' => gT('Keep old value'), 'Y' => gT('Yes'), 'N' => gT('No')], ['disabled'=>'disabled','class'=>'form-select custom-data selector_submitField'] );?>
                </div>
            </div>
		<?php echo TbHtml::hiddenField('changed_by', Yii::app()->user->id, ['class'=>'custom-data']);?>
		<?php echo TbHtml::hiddenField('changed_at', date('Y-m-d H:i:s'), ['class'=>'custom-data']);?>
		
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
