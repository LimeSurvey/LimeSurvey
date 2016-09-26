    <label class="col-sm-2 control-label" for="datepickerInputField_[<?php echo $name; ?>]"><?php echo $defaultname; ?></label>
    <div>
        <div class='col-sm-4'>
        <input class="form-control" name="datepickerInputField_<?php echo $name; ?>" id="datepickerInputField_<?php echo $name; ?>" type="text" value="<?php echo $value; ?>">
        <input name="Attributes[<?php echo $name; ?>]" id="Attributes_<?php echo $name; ?>" type="hidden" value="<?php echo $value; ?>">
            <script type="text/javascript">
                $(function () {
                    $('#datepickerInputField_<?php echo $name; ?>').datetimepicker(datepickerConfig.initDatePickerObject);
                    $('#datepickerInputField_<?php echo $name; ?>').on("dp.change", function(e){
                        $("#Attributes_<?php echo $name; ?>").val(e.date.format(datepickerConfig.dateformatdetailsjs));
                    })
                });
            </script>
        </div>
    </div>
