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
    }
};