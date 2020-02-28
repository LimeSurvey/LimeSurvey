
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
