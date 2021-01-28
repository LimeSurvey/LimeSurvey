<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php foreach ($this->setting['options'] as $value => $text): ?>
        <?php if ($this->setting['value'] == $value): ?>
            <label class="btn btn-default active">
                <input 
                    type="radio" 
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="<?= CHtml::encode($value); ?>"
                    checked
                />
                <?= $text; ?>
            </label>
        <?php else: ?>
            <label class="btn btn-default">
                <input 
                    type="radio" 
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="<?= CHtml::encode($value); ?>"
                />
                <?= $text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
