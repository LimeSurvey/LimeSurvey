<script>
export default {
  data() {},
  computed: {
    files() {
      return this.$store.state.fileList
    },
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
        if (this.$store.state.transitType == "move") {
          htmlClasses += "move ";
        }
        if (this.$store.state.transitType == "copy") {
          htmlClasses += "copy ";
        }
      }
      if (file.selected) {
        htmlClasses += "selected ";
      }
      return htmlClasses;
    },
    inDeletion(file) {
      return this.fileInDeletion == file.path;
    },
    deleteFile(file) {
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
    },
    copyFile(file) {
      this.$store.commit("copyFiles");
      this.$set(file, 'inTransit', true);
       $('[data-toggle="tooltip"]').tooltip('hide');
      //file.inTransit = true;
    },
    moveFile(file) {
      this.$store.commit("moveFiles");
      this.$set(file, 'inTransit', true);
      $('[data-toggle="tooltip"]').tooltip('hide');
      //file.inTransit = true;
    },
    cancelTransit(file) {
      this.$set(file, 'inTransit', false);
      if( this.$store.getters.filesInTransit.length == 0 ) {
        this.$store.commit('noTransit');
        $('[data-toggle="tooltip"]').tooltip('hide');
      }
    }
  }
};
</script>