import ajax from '../mixins/runAjax';
import { LOG } from '../mixins/logSystem';

export default {
    getFolderList: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(window.FileManager.baseUrl+'getFolderList', {surveyid: ctx.state.currentSurveyId})
            .then(
                (result)=>{
                    ctx.commit('setFolderList', result.data);
                    resolve(result);
                }, 
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    getFileList: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(window.FileManager.baseUrl+'getFileList', {surveyid: ctx.state.currentSurveyId, folder: ctx.state.currentFolder})
            .then(
                (result)=>{
                    ctx.commit('setFileList', result.data);
                    resolve(result);
                }
            )
            .catch((error) => {
                LOG.error(error);
                reject(error);
            });
        });
    },
    folderSelected: (ctx, folderObject) => {
        console.log('folderObject', folderObject);
        ctx.commit('setCurrentFolder', folderObject.folder);
        return ctx.dispatch('getFileList');
    },
    deleteFile: (ctx, file) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(
                window.FileManager.baseUrl+'deleteFile', 
                {
                    surveyid: ctx.state.currentSurveyId, 
                    file: file
                }
            ).then(
                (deleteResult) => {
                    ctx.dispatch('getFileList').then(
                        (result)=>{
                            ctx.commit('setFileList', result.data);
                            resolve(result);
                        }, 
                        (error) =>{ reject(error); }
                    )
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    deleteFiles: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(
                window.FileManager.baseUrl+'deleteFiles', 
                {
                    surveyid: ctx.state.currentSurveyId, 
                    files: ctx.getters.filesSelected, 
                }
            ).then(
                (deleteResult) => {
                    ctx.dispatch('getFileList').then(
                        (result)=>{
                            ctx.commit('setFileList', result.data);
                            resolve(result);
                        }, 
                        (error) =>{ reject(error); }
                    )
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    applyTransition: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(
                window.FileManager.baseUrl+'transitFiles', 
                {
                    targetFolder: ctx.state.currentFolder, 
                    surveyid: ctx.state.currentSurveyId,
                    files: ctx.getters.filesInTransit,
                    action: ctx.state.transitType
                }).then(
                (transitResult) => {
                    ctx.dispatch('getFileList').then(
                        (result)=>{
                            ctx.commit('setFileList', result.data);
                            ctx.commit('cancelTransit');
                            resolve(result);
                        }, 
                        (error) =>{ reject(error); }
                    )
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    downloadFiles: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(
                window.FileManager.baseUrl+'downloadFiles', 
                {
                    files: ctx.getters.filesSelected,
                    folder: ctx.state.currentFolder,
                    surveyId: ctx.state.currentSurveyId
                }).then(
                (result) => {
                    const downloadIframe = document.getElementById("fileManager-DownloadFrame");
                    downloadIframe.src = result.data.downloadLink;
                    resolve(result.data.message);
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    }
};
