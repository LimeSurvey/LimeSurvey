<div class="form-row">
    <i class="fa fa-question pull-right" 
        @click="triggerShowHelp=!triggerShowHelp" 
        v-if="(elHelp.length>0) && !readonly" 
        :aria-expanded="!triggerShowHelp" 
        :aria-controls="'help-'+(elName || elId)"
    /></i>
    <label class="form-label">
        <?= $this->generalOption->title; ?>
    </label>
    <div class="input-group col-12">
        <div v-if="hasPrefix" class="input-group-addon">
            <?= $this->generalOption->formElement->options['inputGroup']['prefix']; ?>
        </div>
        <input
            type="text"
            v-model="curValue"
            :pattern="elOptions.elInputPattern"
            :class="getClasses"
            name="<?= $this->generalOption->name; ?>" 
            id="<?= $this->generalOption->name; ?>"
        />
        <?php if (isset($this->generalOption->formElement->options['inputGroup']['suffix'])): ?>
            <div class="input-group-addon">
                <?= $this->generalOption->formElement->options['inputGroup']['suffix']; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
