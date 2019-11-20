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
        state.fileList = newValue;
    },
    setDebug: (state, newValue) => {
        state.debug = newValue;
    },
    setFileRepresentation: (state, newValue) => {
        state.fileRepresentation = newValue;
    },
    copyFile: (state, file) => {
        state.fileInTransit = file;
        state.transitType = 'copy';
    },
    moveFile: (state, file) => {
        state.fileInTransit = file;
        state.transitType = 'move';
    },
    cancelTransit: (state) => {
        state.fileInTransit = null;
        state.transitType = null;
    },
    toggleCollapseFolder: (state, folderShortName) => {
        const tmp = LS.ld.merge([], state.uncollapsedFolders);
        const pos = state.uncollapsedFolders.indexOf(folderShortName);
        if( pos !== -1) {
            tmp.splice(pos, 1);
        } else {
            tmp.push(folderShortName);
        }
        state.uncollapsedFolders = tmp;
    }
};
