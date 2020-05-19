<template>
</template>
<script>
export default {
  data() {
    return {
      isBlocked: false,
    }
  },
  name: 'AbstractRepresentation',
  computed: {
    files() {
      return this.$store.state.fileList
    },
  },
  mounted() {
    LS.EventBus.$on('isBlocked', (blocked) => {
      this.onIsBlocked(blocked);
    });
  },
  methods: {
    inTransit(file) {
      return file.inTransit;
    },
    selectAllFiles() {
      this.$store.commit("markAllFilesSelected");
    },
    fileClass(file) {
      let htmlClasses = "scoped-file-icon ";
      if (this.inDeletion(file)) {
        htmlClasses += "file-in-deletion ";
      }
      if (this.inTransit(file) === true ) {
        htmlClasses += "file-in-transit ";
        if (this.$store.state.transitType === "move") {
          htmlClasses += "move ";
        }
        if (this.$store.state.transitType === "copy") {
          htmlClasses += "copy ";
        }
      }
      if (file.selected) {
        htmlClasses += "selected ";
      }
      return htmlClasses;
    },
    inDeletion(file) {
      return this.fileInDeletion === file.path;
    },
    deleteFile(file) {
      if (!this.isBlocked) {
        this.$dialog
          .confirm(
            this.translate("You are sure you want to delete %s").replace(
              "%s",
              file.shortName
            )
          )
          .then(dialog => {
              this.loadingState = true;
              this.$store
                .dispatch("deleteFile", file)
                    .then(
                      result => {},
                      error => {
                        this.$log.error(error);
                      }
                    )
                    .finally(() => {
                      this.loadingState = false;
                    });
                })
                .catch(() => {
                  this.$log.log("Clicked on cancel");
                });
      }

    },
    copyFile(file) {
      if (!this.isBlocked) {
        this.$store.commit("copyFiles");
        this.$set(file, 'inTransit', true);
        this.isBlocked = true;
        this.hideTooltip();
      }
    },
    moveFile(file) {
      if (!this.isBlocked) {
        this.$store.commit("moveFiles");
        this.$set(file, 'inTransit', true);
        this.isBlocked = true;
        this.hideTooltip();
      }
    },
    cancelTransit(file) {
      if (this.isBlocked) {
        this.$set(file, 'inTransit', false);
        this.isBlocked = false;
        if (this.$store.getters.filesInTransit.length == 0) {
          this.$store.commit('noTransit');
          this.hideTooltip();
        }
      }
    },
    onIsBlocked(blocked) {
      this.isBlocked = blocked;
    },
    hideTooltip() {
      $('[data-toggle="tooltip"]').tooltip('hide');
    }
  },
};
</script>
