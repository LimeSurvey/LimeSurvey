
import mockFolderList from './folderList.json';
import mockFileList from './fileList.json';
import mockFileList2 from './fileList2.json';

export default {
    getFolderList: jest.fn((ctx) => {
        return new Promise((resolve, reject) => {
            ctx.commit('setFolderList', mockFolderList);
            resolve(mockFolderList);
        });
    }),
    getFileList: jest.fn((ctx) => {
        return new Promise((resolve, reject) => {
            ctx.commit('setFileList', (ctx.state.currentFolder == 'generalfiles' ? mockFileList : mockFileList2));
            resolve((ctx.state.currentFolder == 'generalfiles' ? mockFileList : mockFileList2));
        });
    }),
    folderSelected: jest.fn(),
    deleteFile: jest.fn(() => Promise.resolve()),
    applyTransition: jest.fn(() => Promise.resolve()),
};
