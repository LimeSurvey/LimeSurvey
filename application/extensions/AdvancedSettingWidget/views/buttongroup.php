<div class="btn-group col-12" role="group">
    <?php foreach ($this->setting['options'] as $value => $text): ?>
        <?php if ($this->setting['value'] == $value): ?>
            <input 
                class="btn-check"
                type="radio" 
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="<?= CHtml::encode($value); ?>"
                checked
            />
            <label class="btn btn-outline-default">
                <?= $text; ?>
            </label>
        <?php else: ?>
            <input 
                class="btn-check"
                type="radio" 
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="<?= CHtml::encode($value); ?>"
            />
            <label class="btn btn-outline-default">
                <?= $text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
