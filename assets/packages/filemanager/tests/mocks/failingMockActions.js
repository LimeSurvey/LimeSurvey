
import mockFolderList from './folderList.json';
import mockFileList from './fileList.json';
import mockFileList2 from './fileList2.json';

export default {
    getFolderList: jest.fn((ctx) => {
        return new Promise((resolve, reject) => {
            reject({
                data: {
                    message: 'Server not available',
                }
            });
        });
    }),
    getFileList: jest.fn((ctx) => {
        return new Promise((resolve, reject) => {
            reject({
                data: {
                    message: 'Server not available',
                }
            });
        });
    }),
    folderSelected: jest.fn(),
    deleteFile: jest.fn(),
    applyTransition: jest.fn(),
};
