$(document).on('ready  pjax:scriptcomplete', function(){

    //full area where files can be dragged and dropped
    const dropZone = document.getElementById('drop_zone');

    //the normal way to upload files for importing a survey
    const inputFieldFile = document.getElementById('fileUpload');

    //the text field , where the user can see the file names he wants to import
    const textField = document.getElementById('file-upload-text');

    /**
     * Append file name to text field.
     */
    function changeTextAfterFileIsChanged(filename){
        textField.textContent = filename + ('\n');
    }

    inputFieldFile.addEventListener('change', function(event) {
        const files = event.target.files; // This is a FileList object
        for (const file of files) {
            changeTextAfterFileIsChanged(file.name);
        }
    });

    function dropHandler(ev) {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();
        //show file name(s), instead of default text when dropping or adding files to the drop_zone
        const droppedFiles = event.dataTransfer.files;

        // Create a new DataTransfer object to simulate file input
        const dataTransfer = new DataTransfer();
        for (const file of droppedFiles) {
            dataTransfer.items.add(file);
            changeTextAfterFileIsChanged(file.name);
        }
        inputFieldFile.files = dataTransfer.files;
    }

    //to prevent to just open file content in new tab
    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
    });

    dropZone.addEventListener('drop', (event ) => {
        dropHandler(event);
    });

    $('#import-submit').on('submit', function() {
        //Check input fields are filled
        //check file ending
        if (inputFieldFile.files.length === 0) {
            textField.textContent = 'No file selected';
            return false;
        }

        // submit form here
        return true;
    });

});
