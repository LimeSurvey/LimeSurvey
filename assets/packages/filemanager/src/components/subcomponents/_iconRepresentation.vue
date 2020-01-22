<template>
  <div class="container-fluid scoped-table-aloud">
    <div class="masonry-container">
      <div
        v-if="!loading"
        class="ls-flex ls-flex-column scoped-file-tile"
        v-for="file in files"
        :id="'iconRep-' + file.hash"
        :key="file.key"
        :class="fileClass(file)"
      >
        <div class="ls-flex ls-flex-row align-content-center align-items-center">
          <img
            v-if="file.isImage"
            class="scoped-contain-image"
            :src="file.src"
            :alt="file.shortName"
          />
          <i v-else :class="'fa '+file.iconClass+' fa-4x scoped-big-icon'"></i>
        </div>
        <div class="scoped-prevent-overflow ls-space margin top-5">{{file.shortName}}</div>
        <div
          class="ls-flex ls-flex-row align-items-space-between align-content-space-between ls-space margin top-5"
        >
          <div class="text-left ls-flex">
            <small>{{file.size | bytes}}</small>
          </div>
          <div class="text-right ls-flex">
            <small>{{file.mod_time}}</small>
          </div>
        </div>
        <div class="ls-flex ls-flex-row ls-space align-content-space-between margin top-5">
          <div>
            <template v-if="!file.inTransit">
              <button
                class="FileManager--file-action-delete btn btn-default"
                @click="deleteFile(file)"
                :title="translate('Delete file')"
                data-toggle="tooltip"
              >
                <i class="fa fa-trash-o text-danger"></i>
              </button>
              <button
                v-show="$store.state.transitType == 'copy' || $store.state.transitType == null"
                class="FileManager--file-action-startTransit-copy btn btn-default"
                data-toggle="tooltip"
                :title="translate('Copy file')"
                @click="copyFile(file)"
              >
                <i class="fa fa-clone"></i>
              </button>
              <button
                v-show="$store.state.transitType == 'move' || $store.state.transitType == null"
                class="FileManager--file-action-startTransit-move btn btn-default"
                data-toggle="tooltip"
                :title="translate('Move file')"
                @click="moveFile(file)"
              >
                <i class="fa fa-files-o"></i>
              </button>
            </template>
            <template v-if="file.inTransit">
              <button
                class="FileManager--file-action-cancelTransit btn btn-default"
                @click="cancelTransit(file)"
                :title="translate('Cancel transit of file')"
                data-toggle="tooltip"
              >
                <i class="fa fa-times text-warning"></i>
              </button>
            </template>
          </div>
          <div class="text-right">
            <input type="checkbox" v-model="file.selected" />
          </div>
        </div>
      </div>
      <div class="ls-flex-row ls-space padding top-15" v-if="loading">
        <div class="display-relative">
          <loader-widget id="filemanager-loader-widget"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import applyLoader from "../../mixins/applyLoader";
import AbstractRepresentation from "../../helperComponents/abstractRepresentation";

export default {
  name: "tablerep",
  extends: AbstractRepresentation,
  mixins: [applyLoader],
  data() {
    return {
      fileInDeletion: false
    };
  },
  filters: {
    bytes(value) {
      if (value < 1024) {
        return value + " B";
      }
      if (value >= 1024 && value < 1048576) {
        return Math.round((value / 1024) * 100) / 100 + " KB";
      }
      if (value >= 1048576) {
        return Math.round((value / (1024 * 1024)) * 100) / 100 + " MB";
      }
    }
  }
};
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
.scoped-file-icon {
  border: 1px solid black;
  box-shadow: 1px 2px 3px #939393;
  margin: 1.1rem;
  &:first-of-type {
    margin-top: 0;
  }
  padding: 0.5rem;
  &.selected,&.file-in-transit {
    box-shadow: 3px 5px 6px var(--LS-admintheme-hovercolor);
  }
}
</style>
