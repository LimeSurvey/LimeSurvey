import ajax from '../mixins/runAjax';

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
                reject(error);
            });
        });
    },
    folderSelected: (ctx, folderObject) => {
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
    applyTransition: (ctx) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_post(
                window.FileManager.baseUrl+'transitFile', 
                {
                    targetFolder: ctx.state.currentFolder, 
                    surveyid: ctx.state.currentSurveyId, 
                    file: ctx.state.fileInTransit, 
                    action: ctx.state.transitType
                }).then(
                (transitResult) => {
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
    }
};
