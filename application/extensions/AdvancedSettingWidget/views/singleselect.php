<select 
    class="form-control" 
    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
    id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
>
    <?php foreach ($this->setting['options'] as $value => $text) : ?>
        <?php if ($this->setting['value'] == $value): ?>
            <option selected="selected" value="<?= CHtml::encode($value); ?>">
        <?php else: ?>
            <option value="<?= CHtml::encode($value); ?>">
        <?php endif; ?>
        <?= $text; ?>
        </option>
    <?php endforeach; ?>
</select>
