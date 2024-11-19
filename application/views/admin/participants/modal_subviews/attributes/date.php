<div class="mb-3">
    <label class="form-label" for="datepickerInputField_[<?=CHtml::encode($name)?>]"><?php echo $defaultname; ?></label>
    <input class="form-control" name="datepickerInputField_<?=CHtml::encode($name)?>" id="datepickerInputField_<?=CHtml::encode($name)?>" type="text" value="<?=CHtml::encode($value)?>">
    <input name="Attributes[<?=CHtml::encode($name)?>]" id="Attributes_<?=CHtml::encode($name)?>" type="hidden" value="<?=CHtml::encode($value)?>">
    <script type="text/javascript">
        $(function () {
            $('#datepickerInputField_<?=CHtml::encode($name)?>').datetimepicker(datepickerConfig.initDatePickerObject);
            $('#datepickerInputField_<?=CHtml::encode($name)?>').on("dp.change", function(e){
                $("#Attributes_<?=CHtml::encode($name)?>").val(e.date.format(datepickerConfig.dateformatdetailsjs));
            })
        });
    </script>
</div>
