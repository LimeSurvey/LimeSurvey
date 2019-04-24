
<script>
import size from 'lodash/size';
import pick from 'lodash/pick';
import keys from 'lodash/keys';
import slice from 'lodash/slice';
import foreach from 'lodash/forEach';

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
        getInChunks(){
            if(size(this.aLanguages) <= 5) {
                return [this.aLanguages];
            };
            let firstfour = pick(this.aLanguages, slice(keys(this.aLanguages), 0, 4));
            let rest = pick(this.aLanguages, slice(keys(this.aLanguages), 5));
            return [firstfour, rest];
        }
    },
    methods: {
        setCurrentLanguage(newValue) { this.$emit('change', newValue)},
    }
}
</script>
<template>
    <div class="col-xs-12" >                    
        <div class="button-toolbar" :id="elId+'-language-selector'">
            <div 
                class="btn-group" 
            >
                <button 
                    v-for="(languageTerm, language) in getInChunks[0]" 
                    :key="language+'-button'"
                    :class="'btn btn-'+(language==currentLanguage ? 'primary active' : 'default')"
                    @click.prevent="setCurrentLanguage(language)"
                >
                    {{ languageTerm }}
                </button>
                <button
                    v-if="getInChunks.length > 1"
                    class="btn btn-default dropdown-toggle"
                    data-toggle="dropdown"
                >
                    {{ "More Languages" | translate }}
                    <span class="caret"></span>
                </button>
                 <ul class="dropdown-menu">
                    <li
                        v-for="(languageTerm, language) in getInChunks[1]" 
                        :key="language+'-dropdown'"
                        @click.prevent="setCurrentLanguage(language)"
                    >
                        <a href="#">{{ languageTerm }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .button-toolbar>.btn-group {
        margin-top: 0.3rem;
        margin-bottom: 0.5rem;
    }
    .button-toolbar>.btn-group {
        width: 100%;
    }
    .button-toolbar>.btn-group>.btn {
        min-width: 20%;
    }
</style>
