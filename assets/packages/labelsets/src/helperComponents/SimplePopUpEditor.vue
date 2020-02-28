<script>

import merge from 'lodash/merge';

export default {
    name: 'SimplePopUpEditor',
    data(){
        return {
        abstractObject: {},
    }},
    props: {
        typeDef: {type: String, required: true },
        typeDefKey: {type: String, required: true },
        target: {type: String, required: true },
        dataSetObject: {type: Object, required: true},
    },
    computed: {
        
        editorConfig() {
            return {
                'lsExtension:fieldtype': 'labelsets', 
                'lsExtension:ajaxOptions': {},
                'lsExtension:currentFolder':  'upload/global/'
            };
        },
        title() {
            return this.dataSetObject[this.typeDefKey];
        },
    },
    methods: {
        close(){
            this.$emit('close');
        },  
        saveAndClose(){
            this.$emit('modalEvent', {
                target: this.target, 
                method: 'editFromSimplePopupEditor', 
                content: this.abstractObject
            });
            this.$emit('close');
        },
    },
    created() {
        this.abstractObject = merge({},this.dataSetObject);
    }
}
</script>

<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="pagetitle h3">{{title}} - {{'Editor' | translate}} </div>
        </div>
        <div class="panel-body ls-flex-column grow-1 fill">
            <div class="ls-flex-column fill unscoped--SimplePopup-editor-container">
                <lsckeditor class="ls-flex-column fill" v-model="abstractObject[$store.state.activeLanguage][typeDef]" :config="editorConfig"></lsckeditor>
            </div>
        </div>
        <div class="panel-footer">
            <div class="ls-flex-row wrap">
                <div class="ls-flex-item text-right">
                    <button class="btn btn-danger ls-space margin right-5" @click="close" type="button">{{'Cancel' | translate}}</button>
                    <button class="btn btn-primary ls-space margin left-5" @click="saveAndClose" type="button">{{'Save and close' | translate}}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
    .unscoped--SimplePopup-editor-container {
        .ck.ck-editor,
        .ck.ck-editor__main,
        .ck.ck-content {
            display: flex;
            flex-direction: column;
            height:100%;
        }
    }
</style>
