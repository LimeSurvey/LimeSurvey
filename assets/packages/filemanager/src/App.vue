<template>
  <div id="filemanager-app" class="row">
    <div class="container-fluid">
      <nav-bar />
      <div class="row">
        <folder-list @loading="setLoading" @endloading="endLoading" :cols=4 />
        <file-list @loading="setLoading" @endloading="endLoading" :cols=8 />
        <loader-widget id="filemanager-loader-widget" v-if="loading"/>
      </div>
    </div>
  </div>
</template>

<script>
import NavBar from './components/NavBar.vue'
import FolderList from './components/FolderList.vue'
import FileList from './components/FileList.vue'

import LoaderWidget from './helperComponents/loader.vue'

export default {
  name: 'filemanager',
  components: {
    NavBar,
    FolderList,
    FileList,
    LoaderWidget
  },
  data() {
    return {
      loading: true
    }
  },
  methods: {
    setLoading() {
      this.loading = true;
    },
    endLoading() {
      this.loading = false;
    },
  },
  mounted(){
    this.$store.dispatch('getFolderList').then((result) => {
      if(this.$store.state.currentFolder == null ) {
        this.$store.commit('setCurrentFolder', this.$store.state.folderList[0].folder);
      }
      this.$store.dispatch('getFileList').finally(
        ()=>{this.loading = false;}
      );
      }
    );
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
