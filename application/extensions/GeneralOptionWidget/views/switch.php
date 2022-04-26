<div class="btn-group col-12" role="group" data-toggle="buttons">
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
            class="btn btn-outline-primary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
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
            class="btn btn-outline-primary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
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
            class="btn btn-outline-primary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
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
            class="btn btn-outline-primary <?= $this->generalOption->disabled ? 'disabled' : '' ?>"
            for="question[<?= $this->generalOption->name ?>]_N"
        >
            <?= gT('Off') ?>
        </label>
    <?php endif; ?>
</div> 
