<template>
    <div class="container-fluid scoped-table-aloud">
        <div class="ls-flex ls-flex-row row bg-info">
            <div class="ls-flex ls-flex-column col-4 cell">
            {{"File name" | translate }}
            </div>
            <div class="ls-flex ls-flex-column col-1 cell">
            {{"Type" | translate }}
            </div>
            <div class="ls-flex ls-flex-column col-2 cell">
            {{"Size" | translate }}
            </div>
            <div class="ls-flex ls-flex-column col-3 cell">
            {{"Mod time" | translate }}
            </div>
            <div class="ls-flex ls-flex-row col-2 cell">
            {{"Action" | translate }}
            </div>
        </div>
        <div class="ls-flex ls-flex-row row" v-for="file in $store.state.fileList" :key="file.shortName" :class="fileClass(file)">
            <div class="ls-flex ls-flex-column col-4 cell">
            {{file.shortName}}
            </div>
            <div class="ls-flex ls-flex-column col-1 cell">
            <i :class="'fa '+file.iconClass+' fa-lg'"></i>
            </div>
            <div class="ls-flex ls-flex-column col-2 cell">
            {{file.size | bytes}}
            </div>
            <div class="ls-flex ls-flex-column col-3 cell">
            {{file.mod_time}}
            </div>
            <div class="ls-flex ls-flex-row col-2 cell" >
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
      let htmlClasses = 'scoped-file-row ';
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
  .scoped-table-aloud {
    .row {
      margin: 1px 0;
      border-bottom: 1px solid #798979;
    }
    .cell {
      border-left: 1px solid #798979;
      padding: 1rem 0.8rem;
      &:last-of-type {
        border-right: 1px solid #798979;
      }
    }
  }
</style>
