<template>
    <div id="filemanager-app" class="row">
        <x-test id="action::surveyResources"></x-test>
        <div class="container-fluid">
            <nav-bar :loading="loading" @setLoading="setLoading" @forceRedraw="triggerForceRedraw"/>
            <div class="row" v-if="!hasError">
                <folder-list :loading="loading" @setLoading="setLoading" :cols="4" :preset-folder="presetFolder" />
                <file-list :loading="loading" @setLoading="setLoading" :cols="8" />
            </div>
            <div class="row" v-if="hasError">
                <div class="ls-flex ls-flex-column align-content-center align-items-center">
                    <div class="alert alert-warning">{{"An error has happened and no files could be located"|translate}}</div>
                </div>
            </div>
        </div>
        <iframe id="fileManager-DownloadFrame" src="about:blank" frameborder="0" height="0" width="0" />
    </div>
</template>

<script>
import NavBar from "./components/NavBar.vue";
import FolderList from "./components/FolderList.vue";
import FileList from "./components/FileList.vue";

export default {
    name: "filemanager",
    components: {
        NavBar,
        FolderList,
        FileList
    },
    props: {
        presetFolder: {type: String, default: ''}
    },
    data() {
        return {
            loading: true,
            hasError: false
        };
    },
    methods: {
        setLoading(nV) {
            this.$log.log("Loading set on base component");
            this.loading = nV;
        },
        triggerForceRedraw() {
            this.$forceUpdate();
        }
    },
    mounted() {
        this.$store.dispatch("getFolderList").then(result => {
            this.getFolderListResult = result;
            if(this.presetFolder != null) {
                this.$store.commit(
                    "setCurrentFolder",
                    this.presetFolder
                );
            }

            if (this.$store.state.currentFolder == null) {
                this.$store.commit(
                    "setCurrentFolder",
                    this.$store.state.folderList[0].folder
                );
            }
            this.$store.dispatch("getFileList")
            .catch(error => {
                window.LS.notifyFader(
                    `${this.translate("An error has occured and the file list could not be loaded:")}
Error:
${error.data.message}`,
                    'well-lg bg-danger text-center'
                );
            })
            .finally(() => {
                this.loading = false;
            });
        })
        .catch((error) => {
            this.$log.error(error);
            this.loading = false;
            this.hasError = true;
            window.LS.notifyFader(
                `${this.translate("An error has occured and the folders could not be loaded:")}
Error:
${error.data.message}`,
                'well-lg bg-danger text-center'
            );
        });
    }
};
</script>

<style>

</style>
