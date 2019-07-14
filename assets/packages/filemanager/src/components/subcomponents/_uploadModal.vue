
<script>

import vue2Dropzone from 'vue2-dropzone'

export default {
    name: 'UploadModal',
    components: {
        'vue-dropzone': vue2Dropzone,
    },
    props: {},
    data(){
        return {
            dropzoneOptions: {
                url: window.FileManager.baseUrl+'uploadFile',
                thumbnailWidth: 200,
                addRemoveLinks: true
            }
        };
    },
    methods: {
        applyFolderAndData(file, xhr, formData){
            formData.append(LS.data.csrfTokenName, LS.data.csrfToken);
            formData.append('folder', this.$store.state.currentFolder);
            formData.append('surveyid', this.$store.state.currentSurveyId);
        },
        onCompleteHandler(file, response) {
            
            this.$emit('close');
        }
    }
}
</script>

<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="pagetitle h3">{{'Upload a file' | translate}} </div>
        </div>
        <div class="panel-body ls-flex-column grow-1 fill">
            <vue-dropzone 
                ref="fileUploaderDropzone" 
                id="FileUploader--dropzone" 
                v-on:vdropzone-sending="applyFolderAndData" 
                v-on:vdropzone-complete="onCompleteHandler"
                :options="dropzoneOptions" 
                :useCustomSlot="true"
                class="FileUpload--dropzone"
            >
                <div class="dropzone-custom-content">
                    <h3>{{"Drag and drop here, or click once to start uploading" | translate}}</h3>
                    <p>{{"File is uploaded to currently selected folder" | translate}}</p>
                </div>
            </vue-dropzone>
        </div>
    </div>    
</template>

<style scoped>
    .FileUpload--dropzone {
        height: 100%;
    }
</style>
