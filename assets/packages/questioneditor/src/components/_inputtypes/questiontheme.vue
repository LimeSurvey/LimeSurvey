<script>
    import empty from 'lodash/isEmpty';

    import inputTypeMixin from '../../mixins/inputTypeMixin';

    export default {
        name: 'setting-questiontheme',
        mixins: [inputTypeMixin],
        data(){
            return {
                triggerShowHelp: false
            };
        },
        methods: {
            simpleValue(value) {
                if(value == []) {
                    return null;
                }
                return value;
            },
        },
        computed: {
            curValue: {
                get() { return this.currentValue },
                set(newValue) { 
                    this.$emit('change', newValue);
                    this.$store.commit('setStoredEvent', { target: 'GeneralSettings', method: 'toggleTimedLoading', content: true, chain: 'AdvancedSettings' });
                    Promise.all([
                        this.$store.dispatch('getQuestionGeneralSettings'),
                        this.$store.dispatch('getQuestionAdvancedSettings')
                    ]).then((e)=>{
                        this.$store.commit('setStoredEvent', { target: 'GeneralSettings', method: 'toggleTimedLoading', content: false, chain: 'AdvancedSettings' });
                    });
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            getClasses() {
                if(!empty(this.elOptions.classes)) {
                    return this.elOptions.classes.join(' ');
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