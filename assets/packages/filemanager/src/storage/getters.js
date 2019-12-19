export default {
    filesInTransit: (state) => {
        return LS.ld.filter(state.fileList, (file) => file.inTransit );
    },
    filesSelected: (state) => {
        return LS.ld.filter(state.fileList, (file) => file.selected );
    },
};