<div class="btn-group col-12" role="group" data-bs-toggle="buttons">
    <?php foreach ($this->generalOption->formElement->options['options'] as $i => $option): ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <input 
                class="btn-check"
                type="radio" 
                id="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
                name="question[<?= $this->generalOption->name; ?>]" 
                value="<?= $option->value; ?>"
                checked
            />
            <label
                class="btn btn-outline-primary"
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
                value="<?= $option->value; ?>"
            />
            <label
                class="btn btn-outline-primary"
                for="question[<?= $this->generalOption->name; ?>]_<?= $option->value; ?>" 
            >
                <?= $option->text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
