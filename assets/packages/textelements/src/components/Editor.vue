<script>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import debounce from 'lodash/debounce';
import isEqual from 'lodash/isEqual';
import merge from 'lodash/merge';

import runAjax from '../mixins/runAjax.js';

export default {
    name: 'Editor',
    mixins: [runAjax],
    props: {
        label: {type: String, default: ''},
        editorValue: {type: String, default: ''},
        currentEditorConfig: {type: Object, default: {}}
    },
    data() {
        return {
            editorQuestion: editorObject,
            debug: false,
            changeTriggered: debounce((content,event) => {
                this.$log.log('Debounced load triggered',{content,event});
                this.getQuestionPreview();
            }, 3000),
        };
    },
    computed: {
        currentEditorContent: {
            get() {return this.editorValue;},
            set(newValue) {
                this.$emit('change', newValue);
            }
        },
    },
    methods: {
        
    },
}
</script>

<template>
    <div class="col-sm-12 ls-space margin top-5 bottom-5 scope-contains-ckeditor ">
        <label class="col-sm-12">{{ label | translate }}:</label>
        <ckeditor :editor="editorObject" v-model="currentEditorContent" v-on:input="runDebouncedChange" :config="currentEditorConfig"></ckeditor>
    </div>
</template>


<style lang="scss" scoped>
.scope-set-min-height {
    min-height: 40vh;
}
.scope-border-simple {
    border: 1px solid #cfcfcf;
}
.scope-overflow-scroll {
    overflow: scroll;
    height:100%;
    width: 100%;
}
.scope-preview {
    margin: 15px 5px;
    padding: 2rem;
    border: 3px double #dfdfdf;
    min-height: 20vh;
    resize: vertical;
    overflow: auto;
}
.scope-contains-ckeditor {
    min-height: 10rem;
}
</style>
