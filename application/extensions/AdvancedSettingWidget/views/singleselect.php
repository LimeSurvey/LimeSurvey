<select 
    class="form-select" 
    name="<?= $inputBaseName ?>"
    id="<?= CHtml::getIdByName($inputBaseName); ?>"
    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
>
    <?php foreach ($this->setting['options'] as $value => $text) : ?>
        <?php if ($this->setting['value'] == $value): ?>
            <option selected="selected" value="<?= CHtml::encode($value); ?>">
        <?php else: ?>
            <option value="<?= CHtml::encode($value); ?>">
        <?php endif; ?>
        <?= gT($text); ?>
        </option>
    <?php endforeach; ?>
</select>
