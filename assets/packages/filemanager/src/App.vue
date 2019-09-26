<template>
    <div id="filemanager-app" class="row">
        <div class="container-fluid">
            <nav-bar :loading="loading" @setLoading="setLoading" />
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
        presetFolder: {type: String|null, default: null}
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
        }
    },
    mounted() {
        this.$store.dispatch("getFolderList").then(result => {
            
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
            this.$store.dispatch("getFileList").finally(() => {
                this.loading = false;
            });
        })
        .catch((error) => {
            this.$log.error(error);
            this.loading = false;
            this.hasError = true;
        });
    }
};
</script>

<style>
#app {
    font-family: "Avenir", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    margin-top: 60px;
}
</style>
