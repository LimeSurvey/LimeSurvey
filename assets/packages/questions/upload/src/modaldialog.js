window.uploadModalObjects = window.uploadModalObjects || {};

class UploadQuestionController {
    constructor(fieldname) {
        this.fieldname = fieldname
        this.$el = $('#upload_'+fieldname);
        this.$modalEl = $('#file-upload-modal-' + this.fieldname);
        this.show_title = this.$el.data('showtitle');
        this.show_comment = this.$el.data('showcomment');
    }

    prepareOpenUploadModalDialog() {
        const buttonsOpts = {};
        buttonsOpts[uploadLang.returnTxt] = () => {
            this.$el.dialog("close");
        };

        $(document).off('shown.bs.modal.lsuploadquestion', '#file-upload-modal-' + this.fieldname);
        $(document).on('shown.bs.modal.lsuploadquestion', '#file-upload-modal-' + this.fieldname, () => {
            const uploadFrame = $('#uploader' + this.fieldname);
            uploadFrame.load(uploadFrame.data('src'));
            this.updateMaxHeightModalbody(this.$el);
        });

        this.$modalEl.off('hide.bs.modal.lsuploadquestion');
        this.$modalEl.on('hide.bs.modal.lsuploadquestion', () => {
            const uploadFrame = $('#uploader' + this.fieldname);
            window.currentUploadHandler.saveAndExit(this.fieldname, this.show_title, this.show_comment, 1);
            uploadFrame.html('');
            return true;

        });

        this.$el.off('click.lsuploadquestion');
        this.$el.on('click.lsuploadquestion', (e) => {
            console.ls.log("File upload modal opening");
            this.$modalEl.modal('show');
        });
    }

    /* Function to update upload frame
     *
     * @param frameName name of the frame (here it's id too :) )
     * @param integer heigth
     */
    updateUploadFrame(frameName, heigth) {
        $("#" + frameName).innerHeight(heigth);
    }
    /* Function to update modal body max height
     *
     * @param modal jquery object : the modal
     */
    updateMaxHeightModalbody(modal) {
        const modalHeader = $(modal).find(".modal-header").outerHeight();
        const modalFooter = $(modal).find(".modal-footer").outerHeight();
        const finalMaxHeight = Math.max(150, $(window).height() - (modalHeader + modalFooter + 16)); // Not less than 150px
        console.ls.log([$(window).height(), modalHeader, modalFooter, (modalHeader + modalFooter)]);
        $(modal).find(".modal-body").css("max-height", finalMaxHeight);
    }

    getQueryVariable(variable, url) {
        const vars1 = url.split("/");
        for (let i = 0; i < vars1.length; i++) {
            if (vars1[i] == variable) {
                return vars1[i + 1];
            }
        }
        // If not found try with ?
        // TODO : replace by a regexp
        const vars2 = url.replace(/\&amp;/g, '&').split("&");
        for (let i = 0; i < vars.length; i++) {
            const pair = vars2[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return null;
    }

    isValueInArray(arr, val) {
        inArray = false;
        for (let i = 0; i < arr.length; i++) {
            if (val.toLowerCase() == arr[i].toLowerCase()) {
                inArray = true;
            }
        }

        return inArray;
    }

    displayUploadedFiles(filecount, fieldname, show_title, show_comment) {
        const jsonstring = $("#java" + fieldname).val();
        let display = '';

        if (jsonstring == '[]' || jsonstring == '') {
            $('#' + this.fieldname + '_uploadedfiles').addClass('d-none');
            $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').html('');
            return;
        }

        if (jsonstring !== '') {
            let jsonobj = [];
            try {
                jsonobj = JSON.parse(jsonstring);
            } catch (e) {};

            $('#' + this.fieldname + '_uploadedfiles').removeClass('d-none');
            $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').html('');

            const image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico');
            const templateHtml = $('#filerowtemplate_'+this.fieldname).html();
            
            jsonobj.forEach((item, iterator) => {
                let imageOrPlaceholder, imageOrPlaceholderHtml, title, comment, name, filepointer;
                if (this.isValueInArray(image_extensions, item.ext)) {
                    imageOrPlaceholder = "image";
                    imageOrPlaceholderHtml  = '<img src="'+uploadurl+'/filegetcontents/'+decodeURIComponent(item.filename)+'" class="uploaded" />';
                } else {
                    imageOrPlaceholder = "placeholder";
                    imageOrPlaceholderHtml = '<div class="upload-placeholder"></div>';
                }

                title = (show_title != 0) ? htmlentities(item.title) : '';
                comment = (show_comment != 0) ? htmlentities(item.comment) : '';
                name = item.name;
                filepointer = iterator;
                const rowHtml = this.replaceWithObject(templateHtml, {imageOrPlaceholder, imageOrPlaceholderHtml, title, comment, name, filepointer});
                $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').append(rowHtml)
            });
            
            $('.trigger_edit_upload_'+this.fieldname).off('click.lsuploadquestion');
            $('.trigger_edit_upload_'+this.fieldname).on('click.lsuploadquestion', 
            () => {
                this.$modalEl.modal('show');
            });
        }
    };

    replaceWithObject(templateString, objectWithReplacements) {
        let outString = templateString;
        for( let key in objectWithReplacements) {
            outString = outString.replace(new RegExp(`\{${key}\}`), objectWithReplacements[key]);
        }
        return outString;
    }


    showBasic() {
        $('#basic').show();
    }

    hideBasic() {
        $('#basic').hide();
    }
}



window.UploadQuestionController = UploadQuestionController;
