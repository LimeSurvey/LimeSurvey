<div class="form-row">
    <i 
        class="fa fa-question pull-right" 
        @click="triggerShowHelp=!triggerShowHelp" 
        v-if="(elHelp.length>0) && !readonly"
        :aria-expanded="!triggerShowHelp" 
        :aria-controls="'help-'+(elName || elId)"
    ></i>
    <label class="form-label"><?= gT($this->generalOption->title); ?></label>
    <div class="inputtype--toggle-container">
        <input
            type="checkbox"
            name="<?= $this->generalOption->name; ?>"
            id="<?= $this->generalOption->name; ?>"
            />
    </div> 
</div>
