<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php foreach ($this->generalOption->formElement->options['options'] as $i => $option): ?>
        <label class="btn btn-default">
            <input 
                type="radio" 
                name="question[<?= $this->generalOption->name; ?>]" 
                value="<?= $option->value; ?>"
            />
            <?= $option->text; ?>
        </label>
    <?php endforeach; ?>
</div>
