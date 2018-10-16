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
        <div class="form-group">
            <div class="col-sm-1">
                <label class="" >
                <?php eT("Modify"); ?>
                </label>
            </div>
            <div class="col-sm-11"></div>
        </div>
        <?php 
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')
            || $this->user_id == Yii::app()->session['loginID']
            || (Permission::model()->hasGlobalPermission('users', 'update')
                && $this->parent_id == Yii::app()->session['loginID']
            )
        ) {
        ?>
            <div class="form-group">
                <div class="col-sm-1">
                    <label class="" >
                        <input type="checkbox" class="action_check_to_keep_old_value" />
                    </label>
                </div>
                <label class="col-sm-3 control-label"  for='owner_uid'><?php eT("Owner?"); ?></label>
                <div class="col-sm-8">
                    <?php echo TbHtml::dropDownList('owner_uid', 'lskeep', array_merge(['lskeep' => gT('Keep old value')], $model->getOwnerOptions()), ['disabled'=>'disabled','class'=>'custom-data selector_submitField'] );?>
                </div>
            </div>
        <?php } ?>
        <div class="form-group">
            <div class="col-sm-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value" />
                </label>
            </div>
            <label class="col-sm-3 control-label"  for='language'><?php eT("Language?"); ?></label>
            <div class="col-sm-8">
                <?php echo TbHtml::dropDownList('language', 'lskeep', array_merge(['lskeep' => gT('Keep old value')], $model->getLanguageOptions()), ['disabled'=>'disabled','class'=>'custom-data selector_submitField'] );?>
            </div>
        </div>
        
		<div class="form-group">
            <div class="col-sm-1">
                <label class="" >
                    <input type="checkbox" class="action_check_to_keep_old_value" />
                </label>
            </div>
            <label class="col-sm-3 control-label"  for='blacklisted'><?php eT("Blacklisted?"); ?></label>
            <div class="col-sm-8">
                    <?php echo TbHtml::dropDownList('blacklisted', 'lskeep', ['lskeep' => gT('Keep old value'), 'Y' => gT('Yes'), 'N' => gT('No')], ['disabled'=>'disabled','class'=>'custom-data selector_submitField'] );?>
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
        var myFormGroup = $(this).closest('.form-group');
        
        $(this).closest('.form-group').find('input:not(.action_check_to_keep_old_value)').prop('disabled', currentValue)
        $(this).closest('.form-group').find('select').prop('disabled', currentValue)

        if(currentValue){
            $(this).closest('.form-group').find('.selector_submitField').val('lskeep');
        } else {
            $(this).closest('.form-group').find('input.selector_submitField').val('');
        }

    });
</script>
