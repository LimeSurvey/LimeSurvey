<div class="btn-group" role="group" data-bs-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($this->generalOption->name); ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    >
    <?php if ($this->generalOption->formElement->value === 'Y') : ?>
        <input
            class="btn-check"
            type="radio"
            id="question[<?= $this->generalOption->name ?>]_Y"
            name="question[<?= $this->generalOption->name ?>]"
            value="Y"
            <?= $this->generalOption->disabled ? 'disabled' : '' ?>
            checked
        />
        <label
            class="btn btn-outline-secondary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
            for="question[<?= $this->generalOption->name ?>]_Y"
        >
            <?= gT('On') ?>
        </label>
        <input
            class="btn-check"
            type="radio"
            id="question[<?= $this->generalOption->name ?>]_N"
            name="question[<?= $this->generalOption->name ?>]"
            value="N"
            <?= $this->generalOption->disabled ? 'disabled' : '' ?>
            />
        <label
            class="btn btn-outline-secondary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
            for="question[<?= $this->generalOption->name ?>]_N"
        >
            <?= gT('Off') ?>
        </label>
    <?php else : ?>
        <input
            class="btn-check"
            type="radio"
            id="question[<?= $this->generalOption->name ?>]_Y"
            name="question[<?= $this->generalOption->name ?>]"
            value="Y"
            <?= $this->generalOption->disabled ? 'disabled' : '' ?>
            />
        <label
            class="btn btn-outline-secondary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
            for="question[<?= $this->generalOption->name ?>]_Y"
        >
            <?= gT('On') ?>
        </label>
        <input
            class="btn-check"
            type="radio"
            id="question[<?= $this->generalOption->name ?>]_N"
            name="question[<?= $this->generalOption->name ?>]"
            value="N"
            <?= $this->generalOption->disabled ? 'disabled' : '' ?>
            checked
        />
        <label
            class="btn btn-outline-secondary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
            for="question[<?= $this->generalOption->name ?>]_N"
        >
            <?= gT('Off') ?>
        </label>
    <?php endif; ?>
</div> 
