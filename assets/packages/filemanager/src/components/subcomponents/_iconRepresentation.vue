<template>
    <div class="container-fluid scoped-table-aloud">
        <div class="ls-flex ls-flex-row wrap align-content-space-between align-items-space-between">
            <div class="ls-flex ls-flex-column" v-for="file in $store.state.fileList" :key="file.shortName" :class="fileClass(file)">
                <div class="ls-flex ls-flex-row align-content-center align-items-center">
                    <i :class="'fa '+file.iconClass+' fa-4x scoped-big-icon'"></i>
                </div>
                <p>{{file.shortName}}</p>
                <div class="ls-flex ls-flex-row">
                    <div class="text-left ls-flex">
                        <small>{{file.size | bytes}}</small>
                    </div>
                    |
                    <div class="text-left ls-flex">
                        <small>{{file.mod_time}}</small>
                    </div>
                </div>
                <div class="ls-flex ls-flex-row" >
                    <template v-if="!inTransit(file)">
                        <button class="btn btn-default" @click="deleteFile(file)" :title="translate('Delete file')" data-toggle="tooltip"><i class="fa fa-trash-o text-danger"></i></button>
                        <button class="btn btn-default" @click="copyFile(file)" :title="translate('Copy file')" data-toggle="tooltip"><i class="fa fa-clone"></i></button>
                        <button class="btn btn-default" @click="moveFile(file)" :title="translate('Move file')" data-toggle="tooltip"><i class="fa fa-files-o"></i></button>
                    </template>
                    <template  v-if="inTransit(file)">
                        <button class="btn btn-default" @click="cancelTransit(file)" :title="translate('Cancel transit of file')" data-toggle="tooltip"><i class="fa fa-times text-warning"></i></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
  name: 'tablerep',
  data() {return{};},
  filters: {
    bytes(value) {
      if(value < 1024) {
        return value+' B';
      }
      if(value >= 1024 && value < 1048576) {
        return (Math.round((value/1024)*100)/100)+' KB';
      }
      if(value >= 1048576) {
        return (Math.round((value/(1024*1024))*100)/100)+' MB';
      }
    }
  },
  methods: {
    fileClass(file){
      let htmlClasses = 'scoped-file-icon ';
      if(this.inTransit(file)) {
        htmlClasses += 'file-in-transit ';
        if(this.$store.state.transitType == 'move') {
          htmlClasses += ' move ';
        } 
        if(this.$store.state.transitType == 'copy') {
          htmlClasses += ' copy ';
        }
      }
      return htmlClasses;
    },
    inTransit(file) {
      return this.$store.state.fileInTransit != null && file.path == this.$store.state.fileInTransit.path;
    },
    deleteFile(file) {
        this.$dialog.confirm(translate('You are sure you want to delete %s').replace('%s', file.shortName))
        .then(function () {
            this.$emit('loading');
            this.$store.dispatch('deleteFile', file).then(
              (result) => {
                this.$emit('endloading');
              },
              (error) => {
                this.$log.error(error);
                this.$emit('endloading');
              }
            )
        })
        .catch(function () {
          console.log('Clicked on cancel')
        });
    },
    copyFile(file) {
      this.$store.commit('copyFile', file);
    },
    moveFile(file) {
      this.$store.commit('moveFile', file)
    },
    cancelTransit() {
      this.$store.commit('cancelTransit');
    }
  }

}
</script>

<style lang="scss" scoped>
  .scoped-file-icon  {
    border: 1px solid black;
    box-shadow: 1px 2px 3px #939393;
    margin: 1.1rem;
    padding: 0.5rem;
  }
</style>
