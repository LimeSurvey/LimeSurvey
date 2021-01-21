<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php if ($this->generalOption->formElement->value == 'Y') : ?>
        <label class="btn btn-default active">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name; ?>]"
                value="Y"
                checked
                />
            <?= gT('On'); ?>
        </label>
        <label class="btn btn-default">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name; ?>]"
                value="N"
                />
            <?= gT('Off'); ?>
        </label>
    <?php else : ?>
        <label class="btn btn-default">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name; ?>]"
                value="Y"
                />
            <?= gT('On'); ?>
        </label>
        <label class="btn btn-default active">
            <input
                type="radio"
                name="question[<?= $this->generalOption->name; ?>]"
                value="N"
                checked
                />
            <?= gT('Off'); ?>
        </label>
    <?php endif; ?>
</div> 
