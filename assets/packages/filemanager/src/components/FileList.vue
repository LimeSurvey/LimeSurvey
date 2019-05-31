<template>
  <div class="scoped-bordermecrazy" :class="'col-xs-'+cols">
      <div class="container-fluid scoped-table-aloud">
        <div class="row bg-info">
          <div class="col-xs-4 cell">
            {{"File name" | translate }}
          </div>
          <div class="col-xs-1 cell">
            {{"Type" | translate }}
          </div>
          <div class="col-xs-2 cell">
            {{"Size" | translate }}
          </div>
          <div class="col-xs-3 cell">
            {{"Mod time" | translate }}
          </div>
          <div class="col-xs-2 cell">
            {{"Action" | translate }}
          </div>
        </div>
        <div class="row" v-for="file in $store.state.fileList" :key="file.shortName" :class="fileClass(file)">
          <div class="col-xs-4 cell">
            {{file.shortName}}
          </div>
          <div class="col-xs-1 cell">
            <i :class="'fa '+file.iconClass+' fa-lg'"></i>
          </div>
          <div class="col-xs-2 cell">
            {{file.size | bytes}}
          </div>
          <div class="col-xs-3 cell">
            {{file.mod_time}}
          </div>
          <div class="col-xs-2 cell" >
            <template v-if="!inTransit(file)">
              <button class="btn btn-xs" @click="deleteFile(file)" :title="translate('Delete file')" data-toggle="tooltip"><i class="fa fa-trash-o text-danger"></i></button>
              <button class="btn btn-xs" @click="copyFile(file)" :title="translate('Copy file')" data-toggle="tooltip"><i class="fa fa-clone"></i></button>
              <button class="btn btn-xs" @click="moveFile(file)" :title="translate('Move file')" data-toggle="tooltip"><i class="fa fa-files-o"></i></button>
            </template>
            <template  v-if="inTransit(file)">
              <button class="btn btn-xs" @click="cancelTransit(file)" :title="translate('Cancel transit of file')" data-toggle="tooltip"><i class="fa fa-times text-warning"></i></button>
            </template>
          </div>
        </div>
      </div>
      <modals-container/>
  </div>
</template>

<script>
export default {
  name: 'FileList',
  props: {
    cols: {type: Number, default: 6}
  },
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
  .scoped-bordermecrazy {
    border-left: 1px solid grey;
  }
  .scoped-table-aloud {
    .row {
      margin: 0;
      border-bottom: 1px solid #798979;
    }
    .cell {
      border-left: 1px solid #798979;
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
      &:last-of-type {
        border-right: 1px solid #798979;
      }
    }
  }
</style>
