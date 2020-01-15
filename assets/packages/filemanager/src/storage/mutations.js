import map from 'lodash/map';
import merge from 'lodash/merge';
import filter from 'lodash/filter';

export default {
    setCurrentFolder: (state, newValue) => {
        state.currentFolder = newValue;
    },
    setCurrentSurveyId: (state, newValue) => {
        state.currentSurveyId = newValue;
    },
    setFolderList: (state, newValue) => {
        state.folderList = newValue;
    },
    setFileList: (state, newValue) => {
        const currentlyInTransit = filter(state.fileList, (file) => file.inTransit );
        const newFileList = map(newValue, (file, i) => {
            file.key = i;
            file.selected = false;
            file.inTransit = false;
            return file;
        });
        state.fileList = merge(newFileList, currentlyInTransit);
    },
    setDebug: (state, newValue) => {
        state.debug = newValue;
    },
    setFileRepresentation: (state, newValue) => {
        state.fileRepresentation = newValue;
    },
    copyFiles: (state, file) => {
        state.transitType = "copy";
    },
    moveFiles: (state, file) => {
        state.transitType = "move";
    },
    noTransit: (state) => {
        state.transitType = null;
    },
    cancelTransit: state => {
        state.renewIterator = state.renewIterator+1;
        state.transitType = null;
        const tmpList = merge([], state.fileList);

        state.fileList = map(tmpList, (file) => {
            file.key = file.key+''+state.renewIterator;
            file.inTransit = false;
            return file;
        });
    },
    toggleCollapseFolder: (state, folderShortName) => {
        const tmp = LS.ld.merge([], state.uncollapsedFolders);
        const pos = state.uncollapsedFolders.indexOf(folderShortName);
        if (pos !== -1) {
            tmp.splice(pos, 1);
        } else {
            tmp.push(folderShortName);
        }
        state.uncollapsedFolders = tmp;
    },
};
