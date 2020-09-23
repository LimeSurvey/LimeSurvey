<div class="form-row">
    <i
        class="fa fa-question pull-right" 
        @click="triggerShowHelp=!triggerShowHelp" 
        v-if="(elHelp.length>0) && !readonly" 
        :aria-expanded="!triggerShowHelp" 
        :aria-controls="'help-'+(elName || elId)"
    /></i>
    <label class="form-label" :for="elId">
        <?= $this->setting['title']; ?>
    </label>
    <select 
        class="form-control" 
        name="<?= $this->setting['name']; ?>"
        id="<?= $this->setting['name']; ?>"
    >
        <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option): ?>
            <option 
                v-for="(optionObject, i) in elOptions.options.option"
                :key="i"
                :value="<?= json_encode($option['value']); ?>"
            >
                <?= $option['text']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
