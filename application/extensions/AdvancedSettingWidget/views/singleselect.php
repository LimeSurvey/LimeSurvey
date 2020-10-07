<select 
    class="form-control" 
    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
    id="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
>
    <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option) : ?>
        <?php if (!empty($option['value'])) : ?>
            <option value="<?= $option['value']; ?>">
        <?php else : ?>
            <option value="">
        <?php endif; ?>
            <?= $option['text']; ?>
        </option>
    <?php endforeach; ?>
</select>
