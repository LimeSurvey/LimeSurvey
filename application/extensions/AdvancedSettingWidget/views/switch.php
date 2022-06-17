<div class="inputtype--toggle-container">
    <div class="btn-group" role="group" data-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($inputBaseName); ?>"
    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
    >
        <?php if ($this->setting['value'] == "1") : ?>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="<?= $inputBaseName ?>"
                    value="1"
                    checked
                    />
                <?= gT('On'); ?>
            </label>
            <label class="btn btn-default">
                <input
                    type="radio"
                    name="<?= $inputBaseName ?>"
                    value="0"
                    />
                <?= gT('Off'); ?>
            </label>
        <?php else : ?>
            <label class="btn btn-default">
                <input
                    type="radio"
                    name="<?= $inputBaseName ?>"
                    value="1"
                    />
                <?= gT('On'); ?>
            </label>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="<?= $inputBaseName ?>"
                    value="0"
                    checked
                    />
                <?= gT('Off'); ?>
            </label>
        <?php endif; ?>
    </div>
</div>
