<script>
    import empty from 'lodash/isEmpty';
    import first from 'lodash/first';
    import each from 'lodash/forEach';

    import inputTypeMixin from '../../mixins/inputTypeMixin';

    export default {
        name: 'setting-buttongroup',
        mixins: [inputTypeMixin],
        data(){
            return {
                triggerShowHelp: false
            };
        },
        computed: {
            curValue: {
                get() { return this.currentValue },
                set(newValue) { 
                    this.$emit('change', newValue);
                },
            },
            showHelp(){
                return this.triggerShowHelp && (this.elHelp.length>0);
            },
            cleanOptions() {
                if(typeof this.elOptions.options.option == 'object') {
                    return this.elOptions.options.option;
                }

                if(typeof first(this.elOptions.options) == 'object') {
                    return this.elOptions.options;
                }

                const optionsArray = [];
                each(this.elOptions.options, (text, value) => {
                    optionsArray.push({
                        text,
                        value
                    });
                });
                return optionsArray;
            }
        },
        methods: {
            simpleValue(value) {
                if(value == []) {
                    return null;
                }
                return value;
            },
             getHTMLClasses(value) {
                let classes = 'btn btn-default ';
                classes += (value == this.curValue ? 'active ' : '');
                return classes;
            }
        },
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
        <label class="form-label"> {{elLabel}} </label>
        <div class="btn-group col-12">
            <label 
                v-for="(optionObject, i) in cleanOptions"
                :key="i"
                type="button" 
                :for="'input-'+(elName || elId)+'_'+i" 
                :class="getHTMLClasses(optionObject.value)" 
                :disabled="readonly"
            >
                <input 
                    v-if="!readonly"
                    type="radio" 
                    :id="'input-'+(elName || elId)+'_'+i" 
                    v-model="curValue"
                    :name="elName || elId" 
                    :value="simpleValue(optionObject.value)"
                />
                {{optionObject.text}}
            </label>
        </div>
        <transition name="fade">
            <div 
                class="question-option-help well"
                v-show="showHelp"
                :id="'help-'+(elName || elId)" 
                v-html="elHelp"
            />
        </transition>
    </div>
</template>
