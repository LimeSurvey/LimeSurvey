<label class="col-sm-2 control-label" for="datepickerInputField_[<?php echo $name; ?>]"><?php echo $defaultname; ?></label>
    <div>
        <div class='col-sm-4'>
            <select class="form-control" name="Attributes[<?php echo $name; ?>]" id="Attributes_<?php echo $name; ?>">
                <option></option>  <!-- Nothing selected -->
                <?php foreach ($options as $option): ?>
                    <option 
                        <?php if ($option['value'] == $value): echo 'selected'; endif; ?>
                        value='<?php echo $option['value']; ?>'
                    >
                        <?php echo $option['value']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
