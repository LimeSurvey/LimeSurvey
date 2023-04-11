<div class="btn-group" role="group" data-bs-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($this->generalOption->name); ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    >
    <?php foreach ($this->generalOption->formElement->options['options'] as $i => $option): ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <input 
                class="btn-check"
                type="radio" 
                id="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
                name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= CHtml::encode($option->value); ?>"
                checked
            />
            <label
                class="btn btn-outline-secondary"
                for="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
            >
                <?= $option->text; ?>
            </label>
        <?php else : ?>
            <input 
                class="btn-check"
                type="radio" 
                id="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
                name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= CHtml::encode($option->value); ?>"
            />
            <label
                class="btn btn-outline-secondary"
                for="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
            >
                <?= $option->text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
