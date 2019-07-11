<script>
    import empty from 'lodash/isEmpty';
    import inputTypeMixin from '../../mixins/inputTypeMixin';

    export default {
        name: 'setting-text',
        mixins: [inputTypeMixin],
        data(){
            return {
                triggerShowHelp: false
            };
        },
        computed: {
            curValue: {
                get() { return this.currentValue },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ')
                }
                return '';
            }
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
        <div :class="getClasses" :name="elName || elId" :id="elId" v-html="curValue" />
        <div 
            class="question-option-help well"
            :id="'help-'+(elName || elId)"
            v-show="showHelp"
            v-html="elHelp"
        />
    </div>
</template>