<div class="btn-group col-12">
    <?php foreach ($this->generalOption->formElement->options['options']->options as $i => $option): ?>
        <label
            class="btn btn-default"
            type="button"
            >
            <input 
                type="radio" 
                id="'input-'+(elName || elId)+'_'+i" 
                id="input-<?= $this->generalOption->name; ?>-<?= $i; ?>" 
                name="<?= $this->generalOption->name; ?>" 
                value="<?= $option->value; ?>"
            />
            <?= $option->text; ?>
        </label>
    <?php endforeach; ?>
</div>
