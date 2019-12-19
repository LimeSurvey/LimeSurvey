import Vue from 'vue';
import {LOG} from '../mixins/logSystem';
import map from 'lodash/map';
import merge from 'lodash/merge';

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
        LOG.log(newValue);
        state.fileList = map(newValue, (file) => {
            file.selected = false;
            file.inTransit = false;
            return file;
        });
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
    cancelTransit: state => {
        state.transitType = null;
        const tmpFileList = merge([], state.filesList);
        state.filesList = [];
        state.filesList = tmpFileList.map(file => {
            file.inTransit = false;
            return file;
        });
        LOG.log(map(state.filesList, (f) => f.inTransit));
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
    markAllFilesSelected(state) {
        state.filesList = state.filesList.map(file => {
            file.selected = true;
            return file;
        });
    }
};
