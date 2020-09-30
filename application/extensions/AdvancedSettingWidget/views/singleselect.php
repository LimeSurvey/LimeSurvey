<select 
    class="form-control" 
    name="advancedSettings[<?= $this->setting['name']; ?>]"
    id="advancedSettings[<?= $this->setting['name']; ?>]"
>
    <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option) : ?>
        <?php if (isset($option['value'])) : ?>
            <option value="<?= json_encode($option['value']); ?>">
        <?php else : ?>
            <option>
        <?php endif; ?>
            <?= $option['text']; ?>
        </option>
    <?php endforeach; ?>
</select>
