<template>
    <div class="container-fluid scoped-table-aloud">
        <div class="masonry-container" >
            <div 
                class="ls-flex ls-flex-column scoped-file-tile" 
                v-for="file in $store.state.fileList"
                :id="'iconRep-' + file.hash"
                :key="file.shortName" 
                :class="fileClass(file)"
            >
                <div class="ls-flex ls-flex-row align-content-center align-items-center">
                    <img v-if="file.isImage" class="scoped-contain-image" :src="file.src" :alt="file.shortName" />
                    <i v-else :class="'fa '+file.iconClass+' fa-4x scoped-big-icon'"></i>
                </div>
                <div class="scoped-prevent-overflow ls-space margin top-5">
                    {{file.shortName}}
                </div>
                <div class="ls-flex ls-flex-row align-items-space-between align-content-space-between ls-space margin top-5">
                    <div class="text-left ls-flex">
                        <small>{{file.size | bytes}}</small>
                    </div>
                    <div class="text-right ls-flex">
                        <small>{{file.mod_time}}</small>
                    </div>
                </div>
                <div class="ls-flex ls-flex-row ls-space margin top-5" >
                    <template v-if="!inTransit(file)">
                        <button class="FileManager--file-action-delete btn btn-default" @click="deleteFile(file)" :title="translate('Delete file')" data-toggle="tooltip"><i class="fa fa-trash-o text-danger"></i></button>
                        <button class="FileManager--file-action-startTransit-copy btn btn-default" @click="copyFile(file)" :title="translate('Copy file')" data-toggle="tooltip"><i class="fa fa-clone"></i></button>
                        <button class="FileManager--file-action-startTransit-move btn btn-default" @click="moveFile(file)" :title="translate('Move file')" data-toggle="tooltip"><i class="fa fa-files-o"></i></button>
                    </template>
                    <template  v-if="inTransit(file)">
                        <button class="FileManager--file-action-cancelTransit btn btn-default" @click="cancelTransit(file)" :title="translate('Cancel transit of file')" data-toggle="tooltip"><i class="fa fa-times text-warning"></i></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import applyLoader from '../../mixins/applyLoader';

export default {
  name: 'tablerep',
  mixins: [applyLoader],
  data() {return{
      fileInDeletion: false
  };},
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
      if(this.inDeletion(file)) {
        htmlClasses += 'file-in-deletion ';  
      }
      if(this.inTransit(file)) {
        htmlClasses += 'file-in-transit ';
        if(this.$store.state.transitType == 'move') {
          htmlClasses += 'move ';
        } 
        if(this.$store.state.transitType == 'copy') {
          htmlClasses += 'copy ';
        }
      }
      return htmlClasses;
    },
    inDeletion(file) {
        return this.fileInDeletion == file.path;
    },
    inTransit(file) {
      return this.$store.state.fileInTransit != null && file.path == this.$store.state.fileInTransit.path;
    },
    deleteFile(file) {
        this.$dialog.confirm(this.translate('You are sure you want to delete %s').replace('%s', file.shortName))
        .then((dialog) => {
            this.loadingState = true;
            this.$store.dispatch('deleteFile', file).then(
              (result) => {},
              (error) => {this.$log.error(error);}
            ).finally(()=>{ this.loadingState = false; })
        })
        .catch(() => {
          this.$.log.log('Clicked on cancel');
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
    @media (min-width: 769px) {
        // .scoped-file-tile {
        //     width: 20%;
        //     max-height: 550px;
        // }
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

    .file-in-deletion {
        background-color: #999999;
        opacity: 0.5;
    }
   
    .scoped-prevent-overflow {
        overflow: hidden;
        word-wrap: break-word;
    }

    .scoped-contain-image {
        max-width: 100%;
        display: block;
    }
    .scoped-file-icon  {
        border: 1px solid black;
        box-shadow: 1px 2px 3px #939393;
        margin: 1.1rem;
        &:first-of-type {
            margin-top: 0;
        }
        padding: 0.5rem;
    }
</style>
