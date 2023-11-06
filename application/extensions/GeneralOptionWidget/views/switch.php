<div class="btn-group col-12" role="group" data-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($this->generalOption->name); ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    >
    <?php if ($this->generalOption->formElement->value === 'Y') : ?>
        <label class="btn btn-default active <?= $this->generalOption->disabled ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="Y"
                <?= $this->generalOption->disabled ? 'disabled' : '' ?>
                checked
                />
            <?= gT('On') ?>
        </label>
        <label class="btn btn-default <?= $this->generalOption->disabled ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="N"
                <?= $this->generalOption->disabled ? 'disabled' : '' ?>
                />
            <?= gT('Off') ?>
        </label>
    <?php else : ?>
        <label class="btn btn-default <?= $this->generalOption->disabled ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="Y"
                <?= $this->generalOption->disabled ? 'disabled' : '' ?>
                />
            <?= gT('On') ?>
        </label>
        <label class="btn btn-default active <?= $this->generalOption->disabled ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="N"
                <?= $this->generalOption->disabled ? 'disabled' : '' ?>
                checked
                />
            <?= gT('Off') ?>
        </label>
    <?php endif; ?>
</div> 
