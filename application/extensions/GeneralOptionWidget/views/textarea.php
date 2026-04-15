<div class="input-group col-12">
    <?php if (isset($this->generalOption->formElement->options['inputGroup']['prefix'])) : ?>
        <div class="input-group-text">
            <?= $this->generalOption->formElement->options['inputGroup']['prefix']; ?>
        </div>
    <?php endif; ?>
    <textarea
        class="form-control" 
        name="question[<?= $this->generalOption->name; ?>]" 
        id="<?= CHtml::getIdByName($this->generalOption->name); ?>"
        <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
        ><?= CHtml::encode($this->generalOption->formElement->value); ?></textarea>
    <?php if (isset($this->generalOption->formElement->options['inputGroup']['suffix'])) : ?>
        <div class="input-group-text">
            <?= $this->generalOption->formElement->options['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
