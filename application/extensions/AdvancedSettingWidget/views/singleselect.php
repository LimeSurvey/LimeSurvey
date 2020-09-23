<div class="form-row">
    <i class="fa fa-question pull-right"></i>
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
    <div class="question-option-help well" /><?= $this->setting['formElementHelp']; ?></div>
</div>
