<div class="form-row">
    <i 
        class="fa fa-question pull-right" 
        @click="triggerShowHelp=!triggerShowHelp" 
        v-if="(elHelp.length>0) && !readonly" 
        :aria-expanded="!triggerShowHelp" 
        :aria-controls="'help-'+(elName || elId)"
    ></i>
    <label class="form-label">
        <?= $this->generalOption->title; ?>
    </label>
    <select 
        name="<?= $this->generalOption->name; ?>" 
        id="<?= $this->generalOption->name; ?>" 
    >
        <?php foreach ($this->generalOption->formElement->options as $option): ?>
            <option value="<?= $option['value']; ?>"><?= $option['text']; ?></option>
        <?php endforeach; ?>
    </select>
</div>
