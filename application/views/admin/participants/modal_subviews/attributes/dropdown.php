<?php $selectId = 'Attributes_' . CHtml::encode($name); ?>
<label id="<?= $selectId ?>_label" class="form-label" for="<?= $selectId ?>"><?= $defaultname ?></label>
<div class='mb-3'>
    <select class="form-select" name="Attributes[<?= CHtml::encode($name) ?>]" id="<?= $selectId ?>">
        <option></option>  <!-- Nothing selected -->
        <?php foreach ($options as $option): ?>
            <option
                <?= $option['value'] == $value ? 'selected' : '' ?>
                value='<?= CHtml::encode($option['value']) ?>'
            >
                <?= CHtml::encode($option['value']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
