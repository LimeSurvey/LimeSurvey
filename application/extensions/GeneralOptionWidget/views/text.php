<div class="input-group col-12">
    <div class="input-group-text">
        <?= $this->generalOption->formElement->options['inputGroup']['prefix']; ?>
    </div>
    <input
        type="text"
        class="form-control"
        name="question[<?= $this->generalOption->name; ?>]" 
        id="<?= CHtml::getIdByName($this->generalOption->name); ?>"
        value="<?= CHtml::encode($this->generalOption->formElement->value); ?>"
        <?= ($this->generalOption->formElement->help) ? 'aria-describedby="help-' . CHtml::getIdByName($this->generalOption->name) . '"' : "" ?>
        <?php foreach ($this->generalOption->formElement->options['attributes'] as $attributeName => $attributeValue) echo $attributeName . '="' . CHtml::encode($attributeValue) . '"'; ?>
    />
    <?php if (isset($this->generalOption->formElement->options['inputGroup']['suffix'])) : ?>
        <div class="input-group-text">
            <?= $this->generalOption->formElement->options['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
