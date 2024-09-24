<!-- TODO: Advanced setting or not? -->
<!-- TODO: $option should be object? -->

<?php //make your user could not select this anymore (selection ONLY through the modal typeselector) ?>
<select 
    class="form-select"
    name="advancedSettings[question_template][<?= $this->generalOption->name; ?>]"
    id="<?= CHtml::getIdByName($this->generalOption->name); ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    style="display:none"
>
    <?php foreach ($this->generalOption->formElement->options as $option) : ?>
        <?php if ($this->generalOption->formElement->value == $option['value']) : ?>
            <option value="<?= CHtml::encode($option['value']); ?>" selected="selected"><?= $option['text']; ?></option>
        <?php else : ?>
            <option value="<?= CHtml::encode($option['value']); ?>"><?= $option['text']; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>

<?php
if ($this->generalOption->formElement->value == $option['value']) {
    echo ': ' .$this->generalOption->formElement->value;
}
?>
