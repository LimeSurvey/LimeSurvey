
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
        fileAdded(file) {
            
        },
        applyFolderAndData(file, xhr, formData){
            formData.append(LS.data.csrfTokenName, LS.data.csrfToken);
            formData.append('folder', this.$store.state.currentFolder);
            const surveyId = LS.reparsedParameters().combined.surveyid;
            if(surveyId != undefined) {
                formData.append('surveyid', surveyId);
            }
        },
        onErrorHandler(error) {
            this.$log.error('error => ', error);
            let errorMessage = this.translate("File could not be uploaded");
            try {
                const jsonResponse = JSON.parse(error.xhr.response);
                errorMessage = jsonResponse.message;
            } catch(e) {
                this.$log.error(error);
            }
            window.LS.notifyFader(
                errorMessage,
                'well-lg bg-danger text-center'
            );
        },
        onCompleteHandler(response) {
            this.$emit('close');
        }
    }
}
</script>

<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="pagetitle h3">{{'Upload a file' | translate}} </div>
            <div> <b>{{'Allowed file formats' | translate}}</b>: </div>
            <div> {{'File formats' | translate}}. </div>
        </div>
        <div class="panel-body ls-flex-column grow-1 fill">
            <vue-dropzone 
                ref="fileUploaderDropzone" 
                id="FileUploader--dropzone" 
                v-on:vdropzone-sending="applyFolderAndData" 
                v-on:vdropzone-error="onErrorHandler"
                v-on:vdropzone-complete="onCompleteHandler"
                :options="dropzoneOptions" 
                :useCustomSlot="true"
                :uploadMultiple="true"
                class="FileUpload--dropzone"
            >
                <div class="dropzone-custom-content">
                    <h3>{{"Drag and drop here, or click once to start uploading" | translate}}</h3>
                    <p>{{"File is uploaded to currently selected folder" | translate}}</p>
                    <p>{{"A .zip archive will be automatically unpacked on the server" | translate}}</p>
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
