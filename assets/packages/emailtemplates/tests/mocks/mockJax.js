import availableFilesList from './availableFilesList.json';

const __runAjax = (uri, data = {}, method = 'get', dataType = 'json') => {
    return new Promise((resolve, reject) => {
        if(uri.match('getFiles')) {
            resolve(availableFilesList); 
        }

        if(uri.match(/admin\/emailtemplates/g)) {

        }
    });
};

export default {
    post: (uri, data) => {
        return __runAjax(uri, data, 'post');
    },
    get: (uri, data, dataType = null) => {
        return __runAjax(uri, data, 'get', dataType);
    },
    load: (uri, data, method='get') => {
        return __runAjax(uri, data, method, "html");
    },
}
