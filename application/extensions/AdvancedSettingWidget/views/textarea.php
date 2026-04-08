<div class="input-group col-12">
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['prefix'])) : ?>
        <div class="input-group-text">
            <?= $this->setting['aFormElementOptions']['inputGroup']['prefix']; ?>
        </div>
    <?php endif; ?>
    <?php if ($this->setting['i18n']): ?>
        <?php foreach ($this->survey->allLanguages as $lang): ?>
            <div class="lang-hide lang-<?= $lang; ?> col-12">
                <textarea
                    class="form-control" 
                    name="<?= $inputBaseName; ?>[<?= $lang; ?>]"
                    id="<?= CHtml::getIdByName($inputBaseName . "[" . $lang ."]"); ?>"
                    aria-labelledby="label-<?= CHtml::getIdByName($inputBaseName); ?>"
                    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
                    ><?= CHtml::encode($this->setting[$lang]['value']); ?></textarea>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <textarea
            class="form-control" 
            name="<?= $inputBaseName ?>"
            id="<?= CHtml::getIdByName($inputBaseName); ?>"
            <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
            ><?= CHtml::encode($this->setting['value']); ?></textarea>
        <?php endif; ?>
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['suffix'])) : ?>
        <div class="input-group-text">
            <?= $this->setting['aFormElementOptions']['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
