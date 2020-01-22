import filter from 'lodash/filter';

export default {
    filesSelected: (state) => {
        return filter(state.fileList, (file) => file.selected );
    },
    filesInTransit: (state) => {
        return filter(state.fileList, (file) => file.inTransit );
    },
};