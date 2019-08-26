<script>
    import empty from 'lodash/isEmpty';

    import inputTypeMixin from '../../mixins/inputTypeMixin';

    export default {
        name: 'setting-columns',
        mixins: [inputTypeMixin],
        data(){
            return {
                triggerShowHelp: false
            };
        },
        computed: {
            curValue: {
                get() { return this.currentValue || this.elOptions.default || '' },
                set(newValue) {
                    this.$emit('change', newValue);
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ')
                }
                return '';
            },
            hasPrefix(){
                if(!empty(this.elOptions.inputGroup)){
                    return !empty(this.elOptions.inputGroup.prefix);
                }
                return false;
            },
            hasSuffix(){
                if(!empty(this.elOptions.inputGroup)){
                    return !empty(this.elOptions.inputGroup.suffix);
                }
                return false;
            },
        }
    };
</script>

<template>
    <div class="form-row">
        <i
                class="fa fa-question pull-right"
                @click="triggerShowHelp=!triggerShowHelp"
                v-if="(elHelp.length>0) && !readonly"
                :aria-expanded="!triggerShowHelp"
                :aria-controls="'help-'+(elName || elId)"
        />
        <label class="form-label" :for="elId"> {{elLabel}} </label>
        <div class="input-group col-12">
            <div v-if="hasPrefix" class="input-group-addon"> {{elOptions.inputGroup.prefix}} </div>
            <input
                    type="number"
                    v-model="curValue"
                    :class="getClasses"
                    :name="elName || elId"
                    :id="elId"
                    :max="12"
                    :min="1"
                    :readonly="readonly"
            />
            <div v-if="hasSuffix" class="input-group-addon"> {{elOptions.inputGroup.suffix}} </div>
        </div>
        <transition name="fade">
            <div
                    class="question-option-help well"
                    :id="'help-'+(elName || elId)"
                    v-show="showHelp"
                    v-html="elHelp"
            />
        </transition>
    </div>
</template>