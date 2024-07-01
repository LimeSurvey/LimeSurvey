<label class=" form-label" for="datepickerInputField_[<?=CHtml::encode($name)?>]"><?php echo $defaultname; ?></label>
<div class='mb-3'>
    <select class="form-select" name="Attributes[<?=CHtml::encode($name)?>]" id="Attributes_<?=CHtml::encode($name)?>">
        <option></option>  <!-- Nothing selected -->
        <?php foreach ($options as $option): ?>
            <option 
                <?php if ($option['value'] == $value): echo 'selected'; endif; ?>
                value='<?= CHtml::encode($option['value']); ?>'
            >
                <?php echo CHtml::encode($option['value']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
