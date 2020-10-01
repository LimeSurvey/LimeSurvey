<select 
    class="form-control"
    name="question[<?= $this->generalOption->name; ?>]"
    id="<?= $this->generalOption->name; ?>"
>
    <!-- TODO: Fix weird object reference. -->
    <?php foreach ($this->generalOption->formElement->options['options']->options as $option) : ?>
        <option value="<?= $option->value; ?>"><?= $option->text; ?></option>
    <?php endforeach; ?>
</select>
