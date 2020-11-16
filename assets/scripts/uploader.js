$(document).on('ready pjax:scriptcomplete', function () {
    doFileUpload();
    fixParentHeigth();
});

function fixParentHeigth(fieldname, elementHeight)
{
    fieldname = fieldname || '';
    elementHeight = elementHeight || 0;

    if (window != top)
    {
        //~ frameheight=Math.max($(document).height(),$('html').outerHeight()+30,150);
        if (fieldname != '')
        {
            frameheight = Math.max($(document).height() + elementHeight, $('#field' + fieldname + '_listfiles').parent().height());
        } else
        {
            frameheight = Math.max($(document).height() + elementHeight, 150);
        }

        if (jQuery.isFunction(parent.updateUploadFrame))
        {
            parent.updateUploadFrame(window.name, frameheight);
        }
    }
}

function doFileUpload()
{
    var fieldname = $('#ia').val();
    /* Load the previously uploaded files */
    var filecount = window.parent.window.$('#' + fieldname + '_filecount').val();
    $('#' + fieldname + '_filecount').val(filecount);

    var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

    if (filecount > 0)
    {
        var jsontext = window.parent.window.$('#' + fieldname).val();
        var json = eval('(' + jsontext + ')');
        if ($('#field' + fieldname + '_listfiles').length == 0)
        {
            $("<ul id='field" + fieldname + "_listfiles' class='files-list' />").insertAfter("#uploadstatus");
        }
        var i;
        $('#' + fieldname + '_licount').val(filecount);

        for (i = 1; i <= filecount; i++)
        {
            var previewblock = "<li id='" + fieldname + "_li_" + i + "' class='previewblock file-element'>";
            previewblock += "<div class='file-preview'>";
            if (isValueInArray(image_extensions, json[i - 1].ext.toLowerCase()))
                previewblock += "<img src='" + uploadurl + "/filegetcontents/" + json[i - 1].filename + "' class='uploaded' onload='fixParentHeigth(fieldname)' />";
            else
                previewblock += "<div class='upload-placeholder' />";

            previewblock += "<span class='file-name'>" + htmlentities(decodeURIComponent(json[i - 1].name),null,null,false) + "</span>";
            previewblock += "</div>";

            previewblock += "<div class='file-info'><fieldset>";
            if ($('#' + fieldname + '_show_title').val() == 1 || $('#' + fieldname + '_show_comment').val() == 1)
            {
                if ($('#' + fieldname + '_show_title').val() == 1)
                {
                    previewblock += "<div class='form-group'><label class='control-label col-xs-4' for='" + fieldname + "_title_" + i + "'>" + uploadLang.titleFld + "</label>" + "<div class='input-container'><input class='form-control' type='text' value='" + htmlentities(json[i - 1].title,null,null,false) + "' id='" + fieldname + "_title_" + i + "' /></div></div>";
                }
                if ($('#' + fieldname + '_show_comment').val() == 1)
                {
                    previewblock += "<div class='form-group'><label class='control-label col-xs-4' for='" + fieldname + "_comment_" + i + "'>" + uploadLang.commentFld + "</label>" + "<div class='input-container'><input class='form-control' type='text' value='" + htmlentities(json[i - 1].comment,null,null,false) + "' id='" + fieldname + "_comment_" + i + "' /></div></div>";
                }

            }
            previewblock += "<div class='form-group'><div class='col-xs-4'></div><div class='input-container'><a class='btn btn-danger' onclick='deletefile(\"" + fieldname + "\", " + i + ")'><span class='fa fa-trash'></span>&nbsp;" + uploadLang.deleteFile + "</a></div></div>";
            previewblock += "</fieldset></div>";

            previewblock += "<input type='hidden' id='" + fieldname + "_size_" + i + "' value=" + json[i - 1].size + " />" +
                "<input type='hidden' id='" + fieldname + "_name_" + i + "' value=" + htmlentities(json[i - 1].name,null,null,false) + " />" +
                "<input type='hidden' id='" + fieldname + "_file_index_" + i + "' value=" + i + " />" +
                "<input type='hidden' id='" + fieldname + "_filename_" + i + "' value=" + htmlentities(json[i - 1].filename,null,null,false) + " />" +
                "<input type='hidden' id='" + fieldname + "_ext_" + i + "' value=" + htmlentities(json[i - 1].ext) + "  />";

            previewblock += "</li>";

            // add file to the list
            $('#field' + fieldname + '_listfiles').append(previewblock);
            fixParentHeigth(fieldname);
        }
    }

    // The upload button
    var button = $('#button1'), interval;
    new AjaxUpload(button, {
        action: uploadurl + '/sid/' + surveyid + '/preview/' + questgrppreview + '/fieldname/' + fieldname + '/',
        name: 'uploadfile',
        data: $.extend({
                valid_extensions: $('#' + fieldname + '_allowed_filetypes').val(),
                max_filesize: $('#' + fieldname + '_maxfilesize').val(),
                preview: $('#preview').val(),
                surveyid: surveyid,
                fieldname: fieldname,
            }, csrfData
        ),
        onSubmit: function (file, ext) {
            var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());
            var filecount = parseInt($('#' + fieldname + '_filecount').val());
            var allowed_filetypes = $('#' + fieldname + '_allowed_filetypes').val().split(",");

            /* If maximum number of allowed filetypes have already been uploaded,
             * do not upload the file and display an error message ! */
            if (filecount >= maxfiles)
            {
                $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle"></span>&nbsp;' + uploadLang.errorNoMoreFiles + '</p>');
                fixParentHeigth(fieldname);
                return false;
            }

            /* If the file being uploaded is not allowed,
             * do not upload the file and display an error message ! */
            var allowSubmit = false;
            for (var i = 0; i < allowed_filetypes.length; i++)
            {
                //check to see if it's the proper extension
                if (jQuery.trim(allowed_filetypes[i].toLowerCase()) == jQuery.trim(ext.toLowerCase()))
                {
                    //it's the proper extension
                    allowSubmit = true;
                    break;
                }
            }
            if (allowSubmit == false)
            {
                $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle"></span>&nbsp;' + uploadLang.errorOnlyAllowed.replace('%s', $('#' + fieldname + '_allowed_filetypes').val()) + '</p>');
                fixParentHeigth(fieldname);
                return false;
            }

            // change button text, when user selects file
            button.text(uploadLang.uploading);

            // If you want to allow uploading only 1 file at time,
            // you can disable upload button
            this.disable();

            // Uploding -> Uploading. -> Uploading...
            interval = window.setInterval(function () {
                var text = button.text();
                if (text.length < 13)
                {
                    button.text(text + '.');
                } else
                {
                    button.text(uploadLang.uploading);
                }
            }, 400);
        },
        onComplete: function (file, response) {
            button.text(uploadLang.selectfile);
            window.clearInterval(interval);
            // enable upload button
            this.enable();

            // Once the file has been uploaded via AJAX,
            // the preview is appended to the list of files
            try{
                var metadata = jQuery.parseJSON(response);
            } catch(e) {
                /* Suppose we get an HTML error ? Replace whole HTML (without head) */
                $('body').html(response);
                return;
            }
            var count = parseInt($('#' + fieldname + '_licount').val());
            count++;
            $('#' + fieldname + '_licount').val(count);

            var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

            if (metadata.success)
            {
                $('#notice').html('<p class="alert alert-success"><span class="fa fa-success"></span>&nbsp;' + metadata.msg + '</p>');
                if ($('#field' + fieldname + '_listfiles').length == 0)
                {
                    $("<ul id='field" + fieldname + "_listfiles' class='files-list' />").insertAfter("#uploadstatus");
                }
                var previewblock = "<li id='" + fieldname + "_li_" + count + "' class='previewblock file-element'>";

                previewblock += "<div class='file-preview'>";
                if (isValueInArray(image_extensions, metadata.ext.toLowerCase()))
                    previewblock += "<img src='" + uploadurl + "/filegetcontents/" + decodeURIComponent(metadata.filename) + "' class='uploaded'  onload='fixParentHeigth(fieldname)' />";
                else
                    previewblock += "<div class='upload-placeholder' />";
                previewblock += "<span class='file-name'>" + escapeHtml(decodeURIComponent(metadata.name)) + "<span>";
                previewblock += "</div>";

                previewblock += "<div class='file-info'><fieldset>";
                if ($('#' + fieldname + '_show_title').val() == 1 || $('#' + fieldname + '_show_comment').val() == 1)
                {
                    if ($('#' + fieldname + '_show_title').val() == 1)
                    {
                        previewblock += "<div class='form-group'><label class='control-label col-xs-4' for='" + fieldname + "_title_" + count + "'>" + uploadLang.titleFld + "</label>" + "<div class='input-container'><input class='form-control' type='text' value='' id='" + fieldname + "_title_" + count + "' /></div></div>";
                    }
                    if ($('#' + fieldname + '_show_comment').val() == 1)
                    {
                        previewblock += "<div class='form-group'><label class='control-label col-xs-4' for='" + fieldname + "_comment_" + count + "'>" + uploadLang.commentFld + "</label>" + "<div class='input-container'><input class='form-control' type='text' value='' id='" + fieldname + "_comment_" + count + "' /></div></div>";
                    }
                }
                previewblock += "<div class='form-group'><div class='col-xs-4'></div><div class='input-container'><a class='btn btn-danger' onclick='deletefile(\"" + fieldname + "\", " + count + ")'><span class='fa fa-trash'></span>&nbsp;" + uploadLang.deleteFile + "</a></div></div>";
                previewblock += "</fieldset></div>";

                previewblock += "<input type='hidden' id='" + fieldname + "_size_" + count + "' value=" + metadata.size + " />" +
                    "<input type='hidden' id='" + fieldname + "_file_index_" + count + "' value=" + metadata.file_index + " />" +
                    "<input type='hidden' id='" + fieldname + "_name_" + count + "' value=" + metadata.name + " />" +
                    "<input type='hidden' id='" + fieldname + "_filename_" + count + "' value=" + metadata.filename + " />" +
                    "<input type='hidden' id='" + fieldname + "_ext_" + count + "' value=" + metadata.ext + "  />";

                previewblock += "</li>";

                // add file to the list
                $('#field' + fieldname + '_listfiles').prepend(previewblock);
                var filecount = parseInt($('#' + fieldname + '_filecount').val());
                var minfiles = parseInt($('#' + fieldname + '_minfiles').val());
                filecount++;
                var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());
                $('#' + fieldname + '_filecount').val(filecount);

                if (filecount < minfiles) {
                    $('#uploadstatus').html(uploadLang.errorNeedMore.replace('%s', (minfiles - filecount))).removeClass('hidden');
                } else if (filecount < maxfiles) {
                    $('#uploadstatus').html(uploadLang.errorMoreAllowed.replace('%s', (maxfiles - filecount))).removeClass('hidden');
                } else {
                    $('#uploadstatus').html(uploadLang.errorMaxReached).removeClass('hidden');
                }
                fixParentHeigth(fieldname);
                if (filecount >= maxfiles) {
                    //$('#notice').html('<p class="alert alert-success"><span class="fa fa-check"></span>&nbsp;' + uploadLang.errorTooMuch + '</p>');
                }
                fixParentHeigth(fieldname);
            } else {
                $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle"></span>&nbsp;' + metadata.msg + '</p>');
                fixParentHeigth(fieldname);
            }

        }
    });

    // if it has been jst opened, the upload button should be automatically clicked !
    // TODO: auto open using click() not working at all ! :(
}

function isValueInArray(arr, val)
{
    inArray = false;
    for (i = 0; i < arr.length; i++)
        if (val.toLowerCase() == arr[i].toLowerCase())
            inArray = true;

    return inArray;
}

// pass the JSON data from the iframe to the main survey page
function passJSON(fieldname, show_title, show_comment, pos)
{
    var json = "[";
    var filecount = 0;
    var licount = parseInt($('#' + fieldname + '_licount').val());
    var i = 1;
    while (i <= licount)
    {

        if ($("#" + fieldname + "_li_" + i).is(':visible'))
        {
            if (filecount > 0)
                json += ",";
            json += '{ ';

            if ($("#" + fieldname + "_show_title").val() == 1)
                json += '"title":"' + htmlentities($("#" + fieldname + "_title_" + i).val(),null,null,false) + '",';
            if ($("#" + fieldname + "_show_comment").val() == 1)
                json += '"comment":"' + htmlentities($("#" + fieldname + "_comment_" + i).val(),null,null,false) + '",';
            json += '"size":"' + $("#" + fieldname + "_size_" + i).val() + '",' +
                '"name":"' + htmlentities($("#" + fieldname + "_name_" + i).val(),null,null,false) + '",' +
                '"filename":"' + htmlentities($("#" + fieldname + "_filename_" + i).val(),null,null,false) + '",' +
                '"ext":"' + htmlentities($("#" + fieldname + "_ext_" + i).val(),null,null,false) + '"}';

            filecount += 1;
        }
        i += 1;
    }
    json += "]";
    window.parent.window.copyJSON(json, filecount, fieldname, show_title, show_comment, pos);
}

function saveAndExit(fieldname, show_title, show_comment, pos)
{
    var filecount = parseInt($('#' + fieldname + '_filecount').val());
    var minfiles = parseInt($('#' + fieldname + '_minfiles').val());

    if (minfiles != 0 && filecount < minfiles && showpopups)
    {
        var confirmans = confirm(uploadLang.errorNeedMoreConfirm.replace('%s', (minfiles - filecount)));
        if (confirmans)
        {
            passJSON(fieldname, show_title, show_comment, pos);
            return true;
        } else
            return false;
    } else
    {
        passJSON(fieldname, show_title, show_comment, pos);
        return true;
    }
}

function deletefile(fieldname, count)
{

    var file_index;
    var filename = $("#" + fieldname + "_filename_" + count).val();
    var name = $("#" + fieldname + "_name_" + count).val();

    var filecount = parseInt($('#' + fieldname + '_filecount').val());
    var licount = parseInt($('#' + fieldname + '_licount').val());

    fileheight = $("#" + fieldname + "_li_" + count).height();
    $("#" + fieldname + "_li_" + count).remove();

    $.ajax(
        {
            method: "POST",
            url: uploadurl,
            data: $.extend({
                'delete': 1,
                'fieldname': fieldname,
                'filename': filename,
                'name': name,
            }, csrfData)
        })
        .done(function (msg) {
            $('#notice').html('<p class="alert alert-success"><span class="fa fa-check"></span>&nbsp;' + msg + '</p>');
            setTimeout(function () {
                $(".success").remove();
            }, 5000);
            $("#" + fieldname + "_li_" + count).hide();
            filecount--;
            $('#' + fieldname + '_filecount').val(filecount);
            file_index = $("#" + fieldname + "_file_index_" + count).val();
            for (j = count; j <= licount; j++)
            {
                if ($('#' + fieldname + '_li_' + j).is(":visible"))
                {
                    $('#' + fieldname + '_file_index_' + j).val(file_index);
                    file_index++;
                }
            }
            var minfiles = parseInt($('#' + fieldname + '_minfiles').val());
            var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());

            if (filecount < minfiles)
            {
                $('#uploadstatus').html(uploadLang.errorNeedMore.replace('%s', (minfiles - filecount)));
                fixParentHeigth(fieldname, -fileheight);
            } else
            {
                $('#uploadstatus').html(uploadLang.errorMoreAllowed.replace('%s', (maxfiles - filecount)));
                fixParentHeigth(fieldname, -fileheight);
            }
        });
}


function escapeHtml(unsafe)
{
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function htmlentities (string, quote_style, charset, double_encode) {
    // Convert all applicable characters to HTML entities
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/htmlentities
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +   improved by: Rafał Kukawski (http://blog.kukawski.pl)
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');
    // *     returns 1: 'Kevin &amp; van Zonneveld'
    // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
    // *     returns 2: 'foo&#039;bar'
    var hash_map = {},
        symbol = '',
        entity = '',
        self = this;
    string += '';
    double_encode = !!double_encode || double_encode == null;

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    hash_map["'"] = '&#039;';

    if (double_encode) {
        for (symbol in hash_map) {
            entity = hash_map[symbol];
            string = string.split(symbol).join(entity);
        }
    } else {
        string = string.replace(/([\s\S]*?)(&(?:#\d+|#x[\da-f]+|[a-z][\da-z]*);|$)/g, function (ignore, text, entity) {
            return self.htmlentities(text, quote_style, charset) + entity;
        });
    }

    return string;
}

function get_html_translation_table (table, quote_style) {
    // Returns the internal translation table used by htmlspecialchars and htmlentities
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/get_html_translation_table
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte
    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    var entities = {},
        hash_map = {},
        decimal = 0,
        symbol = '';
    var constMappingTable = {},
        constMappingQuoteStyle = {};
    var useTable = {},
        useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: " + useTable + ' not supported');
        // return false;
    }

    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';


    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal);
        hash_map[symbol] = entities[decimal];
    }

    return hash_map;
}