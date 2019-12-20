export default {
    filesSelected: (state) => {
        return LS.ld.filter(state.fileList, (file) => file.selected );
    },
    filesInTransit: (state) => {
        return LS.ld.filter(state.fileList, (file) => file.inTransit );
    },
};