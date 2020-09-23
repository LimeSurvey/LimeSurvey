<div class="form-row">
    <i 
        class="fa fa-question pull-right" 
        @click="triggerShowHelp=!triggerShowHelp" 
        v-if="(elHelp.length>0) && !readonly"
        :aria-expanded="!triggerShowHelp" 
        :aria-controls="'help-'+(elName || elId)"
    ></i>
    <label class="form-label"><?= gT($this->setting['title']); ?></label>
    <div class="inputtype--toggle-container">
        <input
            type="checkbox"
            name="<?= $this->setting['name']; ?>"
            id="<?= $this->setting['name']; ?>"
            />
    </div> 
</div>

