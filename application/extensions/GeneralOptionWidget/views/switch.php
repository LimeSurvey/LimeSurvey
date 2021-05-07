<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php if ($this->generalOption->formElement->value === 'Y') : ?>
        <label class="btn btn-default active <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="Y"
                <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>
                checked
                />
            <?= gT('On') ?>
        </label>
        <label class="btn btn-default <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="N"
                <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>
                />
            <?= gT('Off') ?>
        </label>
    <?php else : ?>
        <label class="btn btn-default <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="Y"
                <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>
                />
            <?= gT('On') ?>
        </label>
        <label class="btn btn-default active <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name ?>]"
                value="N"
                <?= $this->generalOption->disableInActive ? 'disabled' : '' ?>
                checked
                />
            <?= gT('Off') ?>
        </label>
    <?php endif; ?>
</div> 
