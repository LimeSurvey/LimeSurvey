<script>

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    name: 'simpleeditor',
    data(){
        return {
        editor: ClassicEditor,
        editorConfig: {},
    }},
    props: {
        value: {type: String, required: true }
    },
    computed: {
        currentValue: {
            get() { return this.value },
            set(newValue) { this.$emit('change', newValue) }
        }
    }
}
</script>

<template>
    <div class="container-fluid">
        <h3>
            {{title}}
            <button type="button" class="close" @click.prevent="$emit('close')" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </h3>
        <div class="row">
            <div class="col-sm-12 scoped-editor-container-element">
                <ckeditor :editor="editor" v-model="currentValue" :config="editorConfig"></ckeditor>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .scoped-editor-container-element {
        min-height: 10rem;
    }
</style>
