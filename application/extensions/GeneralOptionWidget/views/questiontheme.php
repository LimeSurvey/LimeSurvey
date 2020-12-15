<!-- TODO: Advanced setting or not? -->
<!-- TODO: $option should be object? -->
<select 
    class="form-control"
    name="advancedSettings[question_template][<?= $this->generalOption->name; ?>]"
    id="<?= $this->generalOption->name; ?>" 
>
    <?php foreach ($this->generalOption->formElement->options as $option) : ?>
        <?php if ($this->generalOption->formElement->value == $option['value']) : ?>
            <option value="<?= $option['value']; ?>" selected="selected"><?= $option['text']; ?></option>
        <?php else : ?>
            <option value="<?= $option['value']; ?>"><?= $option['text']; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>
