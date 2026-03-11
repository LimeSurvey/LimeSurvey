<?php $selectId = 'Attributes_' . CHtml::encode($name); ?>
<label id="<?php echo $selectId; ?>_label" class="form-label" for="<?php echo $selectId; ?>"><?php echo $defaultname; ?></label>
<div class='mb-3'>
    <select class="form-select" name="Attributes[<?=CHtml::encode($name)?>]" id="<?php echo $selectId; ?>" aria-labelledby="<?php echo $selectId; ?>_label">
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
