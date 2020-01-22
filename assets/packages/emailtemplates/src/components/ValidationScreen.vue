<script>
import runAjax from '../mixins/runAjax.js';

export default {
    name: 'ValidationScreen',
    mixins: [runAjax],
    props: {
        header: {type: String, default: ''},
    },
    data() {
        return {
            validationContent: '',
            loading: true
        };
    },
    computed: {
    },
    methods: {
        stripScripts(s) {
            const div = document.createElement('div');
            div.innerHTML = s;
            const scripts = div.getElementsByTagName('script');
            let i = scripts.length;
            while (i--) {
                let scriptContent = scripts[i].innerHTML;
                let cleanScript = document.createElement('pre');
                cleanScript.innerHTML = `[script]
${scriptContent}
[/script]`;
                scripts[i].parentNode.appendChild(cleanScript);
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        },
    },
    created(){
        let collecionURI = window.EmailTemplateData.validatorUrl;
        this.$_get(collecionURI, {type: this.$store.state.currentTemplateType, lang: this.$store.state.activeLanguage}, 'html').then(
            (result) => { 
                this.$log.log(result);
                this.validationContent = result.data;
                this.loading = false;
            },
            (error) => {
                this.$log.error(error);
                this.loading = false;
            }
        );
    }
}
</script>

<template>
    <div class="container-fluid scoped-visible-container">
        <div class="row" id="emailtemplates--validation-header" v-if="header != ''">
            <h3>{{header}}</h3>
        </div>
        <div class="row">
            <transition type="fade">
                <div class="scoped-contains-email-validation" v-html="stripScripts(validationContent)" v-show="!loading" />
            </transition>
            <transition type="fade">
                <loader-widget id="validate-template-loader" v-show="loading"/>
            </transition>
        </div>
    </div>
</template>


<style lang="scss" scoped>
.scoped-visible-container {
    height:98%;
    overflow: auto;
    margin: 1% 0;
    padding: 15px;
}
.scoped-contains-email-validation {
    min-height:40vh;
}
</style>
