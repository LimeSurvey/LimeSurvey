<select 
    class="form-control" 
    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
    id="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
>
    <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option) : ?>
        <?php if ($this->setting['formElementValue'] == $option['value']): ?>
            <option selected="selected" value="<?= $option['value']; ?>">
        <?php else: ?>
            <option value="<?= $option['value']; ?>">
        <?php endif; ?>
        <?= $option['text']; ?>
        </option>
    <?php endforeach; ?>
</select>
