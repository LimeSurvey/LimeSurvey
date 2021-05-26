<select
    class="form-control"
    name="question[<?= $this->generalOption->name ?>]"
    id="<?= $this->generalOption->name ?>"
    <?= $this->generalOption->disabled ? 'disabled' : '' ?>
>
    <!-- TODO: Fix weird object reference. -->
    <?php foreach ($this->generalOption->formElement->options['options'] as $option) : ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <option value="<?= $option->value ?>" selected="selected"><?= $option->text ?></option>
        <?php else : ?>
            <option value="<?= $option->value ?>"><?= $option->text ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>
