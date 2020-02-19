<template>
  <div class="container-fluid scoped-table-aloud">
    <div class="ls-flex ls-flex-row row bg-info head-row">
      <div class="ls-flex ls-flex-column cell checkbox">
        &nbsp;
      </div>
      <div class="ls-flex ls-flex-column col-4 cell">{{"File name" | translate }}</div>
      <div class="ls-flex ls-flex-column col-1 cell">{{"Type" | translate }}</div>
      <div class="ls-flex ls-flex-column col-2 cell">{{"Size" | translate }}</div>
      <div class="ls-flex ls-flex-column col-3 cell">{{"Mod time" | translate }}</div>
      <div class="ls-flex ls-flex-row col-2 cell">{{"Action" | translate }}</div>
    </div>
    <div
      v-if="!loading"
      class="ls-flex ls-flex-row row"
      v-for="file in files"
      :key="file.key"
      :id="'file-row-'+file.hash"
      :class="fileClass(file)"
    >
      <div class="ls-flex ls-flex-column text-center cell checkbox">
        <input type="checkbox" v-model="file.selected" />
      </div>
      <div class="ls-flex ls-flex-column col-4 cell">{{file.shortName}}</div>
      <div class="ls-flex ls-flex-column col-1 cell">
        <i :class="'fa '+file.iconClass+' fa-lg'"></i>
      </div>
      <div class="ls-flex ls-flex-column col-2 cell">{{file.size | bytes}}</div>
      <div class="ls-flex ls-flex-column col-3 cell">{{file.mod_time}}</div>
      <div class="ls-flex ls-flex-row col-2 cell">
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
            v-show="$store.state.transitType == 'copy'|| $store.state.transitType == null"
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
    </div>
    <div class="ls-flex-row ls-space padding top-15" v-if="loading"> 
      <div class="display-relative">
        <loader-widget id="filemanager-loader-widget"/>
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
  },
};
</script>

<style lang="scss" scoped>
.file-in-deletion {
  background-color: var(--LS-admintheme-hintedhovercolor);
  opacity: 0.5;
}
.file-in-transit {
  background-color: var(--LS-admintheme-hintedbasecolor);
  opacity: 0.7;
}
.scoped-table-aloud {
  .row {
    margin: 1px 0;
    border-bottom: 1px solid #798979;
    &.head-row {
      color: #efefef;
    }
  }
  .cell {
    border-left: 1px solid #798979;
    padding: 1rem 0.8rem;
    &:last-of-type {
      border-right: 1px solid #798979;
    }
    &.checkbox {
      width:41px;
      &>input {
        margin: auto;
      }
    }
  }
}
</style>
