$(document).on('ready  pjax:scriptcomplete', function(){

    //full area where files can be dragged and dropped
    const dropZone = document.getElementById('drop_zone');

    //the hidden input field for dropped files
    const inputFieldDropFile = document.getElementById('dropFile');

    //the normal way to upload files for importing a survey
    const inputFieldFile = document.getElementById('fileUpload');

    //the text field , where the user can see the file names he wants to import
    const textField = document.getElementById('file-upload-text');

    /**
     * Append file name to text field.
     */
    function changeTextAfterFileIsChanged(filename){
        textField.textContent += filename + ('\n');
    }

    inputFieldFile.onchange = function () {
        changeTextAfterFileIsChanged('test output textfield...');
    };



    function dropHandler(ev) {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();
        //show file name(s), instead of default text when dropping or adding files to the drop_zone
        let textField = document.getElementById('file-upload-text');
        let fileNames = '';

        if (ev.dataTransfer.items) {
            [...ev.dataTransfer.items].forEach((item, i) => {
                // If dropped items aren't files, reject them
                if ((item.kind === 'file')) {
                    const file = item.getAsFile();
                    fileNames += file.name + '\n';
                    //todo: assign the file to hidden input field
                    //let reader = new FileReader();
                    //let outputFileUrl = reader.readAsDataURL(file);
                    //inputField.value = outputFileUrl;
                }
            });
        } else {
            [...ev.dataTransfer.files].forEach((file, i) => {
                fileNames += file.name + '\n';
            });
        }
        if(fileNames.trim() !== ''){
            textField.textContent = fileNames;
        }

        // put file to the input field
    }

    //to prevent to just open file content in new tab
    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
    });

    dropZone.addEventListener('drop', (event ) => {
        dropHandler(event);
    });

});
