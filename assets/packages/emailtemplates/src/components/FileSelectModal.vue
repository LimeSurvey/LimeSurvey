<script>
import runAjax from '../mixins/runAjax';

export default {
    name: 'FileSelectModal',
    mixins: [runAjax],
    data() {
        return {
            selectedFiles: [],
            availableFilesList: [],
            loading: true
        }
    },
    computed: {
        filesAvailable() {
            return LS.ld.size(this.availableFilesList) > 0;
        }
    },
    methods: {
        getAvailableFilesList() {
            return new Promise((resolve, reject) => {
                this.$_get(
                    window.EmailTemplateData.getFileUrl, 
                    {
                        surveyid: window.EmailTemplateData.surveyid, 
                        folder: window.EmailTemplateData.surveyFolder
                        }
                ).then(
                    (result)=>{
                        this.$log.log(result);
                        this.availableFilesList = LS.ld.values(result.data);
                        resolve(result);
                    }, 
                    (error) =>{ reject(error); }
                );
            });
        },
        isSelected(file) {
            return (LS.ld.filter(this.selectedFiles, (curFile) => curFile.hash == file.hash)).length > 0;
        },
        toggleFileSelection(file) {
            if(this.isSelected(file)) {
                this.selectedFiles = LS.ld.filter(this.selectedFiles, (curFile) => curFile.hash !== file.hash)
            } else {
                this.selectedFiles.push(file)
            }
        },
        saveAttachments() {
            this.$store.commit('setAttachementForTypeAndLanguage', this.selectedFiles);
            this.$emit('close');
        }
    },
    mounted() {
        this.getAvailableFilesList().then(
            () => { this.loading = false; }
        )
    }
}
</script>

<template>
    <div class="panel panel-default ls-flex-column fill">
        <div class="panel-heading">
            <div class="pagetitle h3"> {{"Select attachement" | translate }} </div>
            <div class="h4"> {{"To add files please open the resources tab, or ask an administrator to add files to the survey folder" | translate }} </div>
        </div>
        <div class="panel-body ls-flex-column grow-1 fill">
            <div class="container-fluid">
                <div class="masonry-container" v-if="!loading && filesAvailable">
                    <div 
                        class="ls-flex ls-flex-column scoped-file-tile scoped-file-icon" 
                        v-for="file in availableFilesList"
                        :class="isSelected(file) ? 'scope-selected-file' : ''"
                        :id="'iconRep-' + file.hash"
                        :key="file.shortName" 
                    >
                        <div 
                            class="ls-flex ls-flex-row align-content-center align-items-center emailtemplates--imagecontainer"
                            @click="toggleFileSelection(file)"
                        >
                            <img v-if="file.isImage" class="scoped-contain-image" :src="file.src" :alt="file.shortName" />
                            <i v-else :class="'fa '+file.iconClass+' fa-4x scoped-big-icon'"></i>
                        </div>
                        <div class="scoped-prevent-overflow ls-space margin top-5">
                            {{file.shortName}}
                        </div>
                    </div>
                </div>

                <div id="fileSelectorLoader" v-if="!loading && !filesAvailable" >
                    <p class="well"> {{"No files in the survey folder"|translate}}</p>
                </div>

                <loader-widget id="fileSelectorLoader" v-if="loading" />
            </div>
        </div>
        <div class="panel-footer">
            <div class="row">
                <div class="col-xs-12 text-right">
                    <button 
                        class="btn btn-success"
                        id="emailtemplates--save-attachements"
                        @click.prevent="saveAttachments"
                    >
                        {{"Save selection" | translate}}
                    </button>
                </div>  
            </div>
        </div>
    </div> 
</template>

<style lang="scss" scoped>
    @media (min-width: 769px) {
        .masonry-container {
            columns: 4 auto;
            column-gap: 1rem;
        }
    }
    @media (max-width: 768px) {
        .masonry-container {
            columns: 2 auto;
            column-gap: 1rem;
        }
    }
    .scoped-contain-image {
        max-width: 100%;
        display: block;
    }
    .scoped-file-icon  {
        border: 1px solid black;
        box-shadow: 1px 2px 3px #939393;
        margin: 1.1rem;
        padding: 0.5rem;

        &:first-of-type {
            margin-top: 0;
        }
        &.scope-selected-file {
            border: 1px solid var(--LS-admintheme-basecolor);
            box-shadow: 3px 6px 9px var(--LS-admintheme-basecolor);
        }
    }
</style>
