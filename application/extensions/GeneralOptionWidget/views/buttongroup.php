<div class="btn-group col-12" role="group" data-toggle="buttons"
    aria-labelledby="label-<?= CHtml::getIdByName($this->generalOption->name); ?>"
    <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
    >
    <?php foreach ($this->generalOption->formElement->options['options'] as $i => $option): ?>
        <?php if ($this->generalOption->formElement->value == $option->value) : ?>
            <label class="btn btn-default active">
                <input 
                    type="radio" 
                    name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= CHtml::encode($option->value); ?>"
                    checked="checked"
                />
                <?= $option->text; ?>
            </label>
        <?php else : ?>
            <label class="btn btn-default">
                <input 
                    type="radio" 
                    name="question[<?= $this->generalOption->name; ?>]" 
                    value="<?= CHtml::encode($option->value); ?>"
                />
                <?= $option->text; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
