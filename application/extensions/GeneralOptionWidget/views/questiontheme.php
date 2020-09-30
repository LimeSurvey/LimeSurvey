<select 
    class="form-control"
    name="generalSettings[<?= $this->generalOption->name; ?>]"
    id="<?= $this->generalOption->name; ?>" 
>
    <?php foreach ($this->generalOption->formElement->options as $option) : ?>
        <option value="<?= $option['value']; ?>"><?= $option['text']; ?></option>
    <?php endforeach; ?>
</select>
