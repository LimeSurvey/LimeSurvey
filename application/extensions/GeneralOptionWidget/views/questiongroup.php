<div class="form-row">
    <i class="fa fa-question pull-right" ></i>
    <label class="form-label" :for="elId">
        <?= $this->generalOption->title; ?>
    </label>
    <select 
        v-model="curValue"
        :class="getClasses" 
        :name="elName || elId" 
        :id="elId" 
        :disabled="readonly"
    >
        <option 
            v-for="(optionObject, i) in elOptions.options"
            :key="i"
            :value="simpleValue(optionObject.value)"
        >
            {{optionObject.text}}
        </option>
    </select>
    <div class="question-option-help well" /><?= $this->generalOption->formElement->help; ?></div>
</div>

