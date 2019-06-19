export default class LSFileUploadAdapter {
    constructor(loader, editor) {
        // The file loader instance to use during the upload.
        this.loader = loader;
        this.editor = editor;
    }

    // Starts the upload process.
    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                const formData = new FormData();
                const ajaxOptions = this.editor.config.get('lsReplacement:ajaxOptions');

                formData.append("file", file, file.name);
                formData.append("folder", this.editor.config.get('lsExtension:currentFolder'));
                LS.ld.forEach(ajaxOptions, (option, key) => {
                    formData.append(option, key);
                });

                $.ajax({
                    type: "POST",
                    url: LS.createUrl('admin/filemanager/sa/uploadFile'),
                    data: formData,
                    xhr: () => {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', this._progressHandling, false);
                        }
                        return myXhr;
                    },
                    success: (data) => {
                        resolve({
                            default: data.src
                        });
                    },
                    error: (error) => {
                        reject(error);
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000
                });
            }));
    }

    _progressHandling(event) {
        console.log(event);
    }
}
