export default class LSFileUploadAdapter {
    constructor(loader, editor) {
        // The file loader instance to use during the upload.
        this.loader = loader;
        this.editor = editor;
    }

    // Starts the upload process.
    upload() {
    }

    _progressHandling(event) {
        console.log(event);
    }
}
