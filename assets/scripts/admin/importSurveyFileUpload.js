$(document).on('ready  pjax:scriptcomplete', function(){

    const fileInput = document.getElementById('drop_zone');
    const inputField = document.getElementById('the_file');

    inputField.onchange = function() {
        let textField = document.getElementById('file-upload-text');
        if(inputField.files.length > 0) {
            //collect all file names from the input field and show them in the drop_zone
            textField.textContent = Array.from(inputField.files, file => file.name).join('\n');
        }
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
    }

    fileInput.addEventListener('dragover', (event) => {
        event.preventDefault();
    });
    fileInput.addEventListener('drop', (event ) => {
        dropHandler(event);
    });

});
