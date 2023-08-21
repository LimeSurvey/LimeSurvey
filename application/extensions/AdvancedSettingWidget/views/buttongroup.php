<div class="btn-group" role="group"
    aria-labelledby="label-<?= CHtml::getIdByName($inputBaseName); ?>"
    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
    >
    <?php foreach ($this->setting['options'] as $value => $text): ?>
        <?php if ($this->setting['value'] == $value): ?>
            <input 
                class="btn-check"
                type="radio" 
                name="<?= $inputBaseName ?>"
                id="<?= $inputBaseName . $value ?>"
                value="<?= CHtml::encode($value); ?>"
                checked
            />
            <label class="btn btn-outline-secondary" for="<?= $inputBaseName  . $value ?>">
                <?= gT($text); ?>
            </label>
        <?php else: ?>
            <input 
                class="btn-check"
                type="radio" 
                name="<?= $inputBaseName ?>"
                id="<?= $inputBaseName . $value ?>"
                value="<?= CHtml::encode($value); ?>"
            />
            <label class="btn btn-outline-secondary" for="<?= $inputBaseName . $value ?>">
                <?= gT($text); ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
