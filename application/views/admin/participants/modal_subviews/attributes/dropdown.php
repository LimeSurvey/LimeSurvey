<label class=" control-label" for="datepickerInputField_[<?=CHtml::encode($name)?>]"><?php echo $defaultname; ?></label>
    <div>
        <div class=''>
            <select class="form-control" name="Attributes[<?=CHtml::encode($name)?>]" id="Attributes_<?=CHtml::encode($name)?>">
                <option></option>  <!-- Nothing selected -->
                <?php foreach ($options as $option): ?>
                    <option 
                        <?php if ($option['value'] == $value): echo 'selected'; endif; ?>
                        value='<?=$option['value']; ?>'
                    >
                        <?php echo $option['value']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
