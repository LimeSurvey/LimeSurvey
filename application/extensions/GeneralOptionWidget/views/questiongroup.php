<select
    class="form-select"
    name="question[<?= $this->generalOption->name ?>]"
    id="<?= CHtml::getIdByName($this->generalOption->name) ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    <?= $this->generalOption->disabled ? 'disabled' : '' ?>
>
    <!-- TODO: Fix weird object reference. -->
    <?php foreach ($this->generalOption->formElement->options['options'] as $option) : ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <option value="<?= CHtml::encode($option->value) ?>" selected="selected"><?= $option->text ?></option>
        <?php else : ?>
            <option value="<?= CHtml::encode($option->value) ?>"><?= $option->text ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>
