<div class="input-group col-12">
    <div class="input-group-addon">
        <?= $this->generalOption->formElement->options['inputGroup']['prefix']; ?>
    </div>
    <input
        type="text"
        class="form-control"
        name="question[<?= $this->generalOption->name; ?>]" 
        id="<?= $this->generalOption->name; ?>"
    />
    <?php if (isset($this->generalOption->formElement->options['inputGroup']['suffix'])) : ?>
        <div class="input-group-addon">
            <?= $this->generalOption->formElement->options['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
