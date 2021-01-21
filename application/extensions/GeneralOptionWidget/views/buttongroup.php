<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php foreach ($this->generalOption->formElement->options['options'] as $i => $option): ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <label class="btn btn-default active">
                <input 
                    type="radio" 
                    name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= $option->value; ?>"
                    checked="checked"
                />
                <?= $option->text; ?>
            </label>
        <?php else : ?>
            <label class="btn btn-default">
                <input 
                    type="radio" 
                    name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= $option->value; ?>"
                />
                <?= $option->text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
