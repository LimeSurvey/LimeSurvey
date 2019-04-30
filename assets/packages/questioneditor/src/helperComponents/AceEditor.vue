<script>
export default {
    name: 'AceEditor',
    props: {
        value: {
            required: true
        },
        thisId: {
            type: String,
            default: ()=>`vue-ace-editor-${Math.round(Math.random()*100000)}`
        },
        options: {type: Object, default: ()=>null},
        showLangSelector: {type: Boolean, default: true},
        showThemeToggle: {type: Boolean, default: true},
        baseLang: {type: String, default: 'html'}
    },
    data: function() {
        return {
            lang: 'html',
            darkMode: false,
            editor: null,
            contentBackup: "",
        };
    },
    methods: {
        toggleDarkMode(){
            if(this.darkMode) {
                this.editor.setTheme("ace/theme/solarized_light");
            } else {
                this.editor.setTheme("ace/theme/solarized_dark");
            }
            this.darkMode = !this.darkMode;
        }
    },
    watch: {
        lang: function(newLang) {
            this.editor.getSession().setMode("ace/mode/" + newLang);
        },
    },
    beforeDestroy: function() {
        this.editor.destroy();
        this.editor.container.remove();
    },
    created(){
        this.lang = this.baseLang;
    },
    mounted() {
        this.editor = ace.edit(this.thisId);

        this.$emit("init", this.editor);

        this.editor.$blockScrolling = Infinity;
        this.editor.setOption("enableEmmet", true);
        this.editor.getSession().setMode("ace/mode/" + this.lang);
        this.editor.setTheme("ace/theme/solarized_light");
        this.editor.setValue(this.value, 1);
        this.contentBackup = this.value;

        editor.on("change", () => {
            let content = editor.getValue();
            this.$emit("change", content);
            this.contentBackup = content;
        });
        if (this.options) {
            this.editor.setOptions(this.options);
        }
        $('#'+this.thisId).on('contextmenu', (e)=>{
            e.preventDefault();
            e.stopPropagation();
            return false;
        })
    }
};
</script>

<template>
    <div class="aceEditor--main">
        <div class="aceEditor--topbar">
            <div class="aceEditor--subcontainer">
                <button @click="toggleDarkMode" class="btn btn-xs btn-default"> {{'Toggle dark mode'|translate}}</button>
            </div>
            <div class="aceEditor--subcontainer">
                <select class="aceEditor--langselect" v-model="lang">
                    <option value="html">HTML</option>
                    <option value="javascript">JavaScript</option>
                    <option value="css">CSS</option>
                </select>
        </div>
        </div>
        <div class="aceEditor--editor" :id="thisId"></div>
    </div>
</template>

<style lang="scss" scoped>
    .aceEditor--main {
        .aceEditor--topbar{
            height: 3rem;
            padding: 0.2rem;
            background-color: rgb(196, 196, 196);
            display: flex;
            flex-wrap: nowrap;
            align-items: space-around;
            align-content: space-around;
            .aceEditor--subcontainer {
                flex: 1;
                &:first-child {
                    text-align: left;
                }
                &:last-child {
                    text-align: right;
                }
            }
            .aceEditor--darkmodeswitch {
                border-radius: 0;
                align-self: flex-start;
                padding: 0.4rem;
                height: 2.2rem;
                margin: 0.2rem;
            }

            .aceEditor--langselect {
                border-radius: 0;
                align-self: flex-end;
                height: 2.2rem;
                margin: 0.2rem;
            }
        }

        .aceEditor--editor {
            min-height: 10rem;
        }
    }
</style>
