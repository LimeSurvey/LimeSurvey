
<script>
import size from 'lodash/size';
export default {
    name: 'language-selector',
    props: {
        elId: {type: String, required: true},
        aLanguages: {type: [Array, Object], required: true},
        parentCurrentLanguage: {type: String, required: true}
    },
    computed: {
        currentLanguage: {
            get() { return this.parentCurrentLanguage},
            set(newValue) { this.$emit('change', newValue)}
        },
        lessThanFour(){
            return size(this.aLanguages) < 4;
        }
    },
    methods: {
        setCurrentLanguage(newValue) { this.$emit('change', newValue)},
    }
}
</script>
<template>
    <div class="col-xs-12" >                    
        <template v-if="lessThanFour">
            <div class="button-toolbar" :id="elId+'-language-selector'">
                <div 
                    class="btn-group" 
                    v-for="(languageTerm, language) in aLanguages" 
                    :key="language"
                    :class="language==currentLanguage ? ' active' : ''"
                >
                    <button 
                        :class="'btn btn-'+(language==currentLanguage ? 'primary' : 'default')"
                        @click.prevent="setCurrentLanguage(language)"
                    >
                        {{ languageTerm }}
                    </button>
                </div>
            </div>
        </template>
        <template v-else>
            <select
                class="form-control"
                :id="elId+'-language-selector'"
                :name="elId+'-language-selector'"
                v-model="currentLanguage"
            >
                <option
                    v-for="(languageTerm, language) in aLanguages" 
                    :key="language"
                    :value="language"
                >
                    {{ languageTerm }}
                </option>
            </select>
        </template>
    </div>
</template>

<style scoped>
    select {
        min-width: 80%;
    }
    .button-toolbar>.btn-group {
        margin: 0.25em 1em;
        min-width: 18%;
    }
    .button-toolbar>.btn-group:first {
        margin: 0em 1em 0.25em 1em;
    }
    button {
        width: 100%
    }
</style>
