<div class="btn-group col-12" role="group" data-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($inputBaseName); ?>"
    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
    >
    <?php foreach ($this->setting['options'] as $value => $text): ?>
        <?php if ($this->setting['value'] == $value): ?>
            <label class="btn btn-default active">
                <input 
                    type="radio" 
                    name="<?= $inputBaseName ?>"
                    value="<?= CHtml::encode($value); ?>"
                    checked
                />
                <?= gT($text); ?>
            </label>
        <?php else: ?>
            <label class="btn btn-default">
                <input 
                    type="radio" 
                    name="<?= $inputBaseName ?>"
                    value="<?= CHtml::encode($value); ?>"
                />
                <?= gT($text); ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
