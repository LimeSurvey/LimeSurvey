$(document).ready(function() {
//    $('#basic').hide();
//    var jsonstring = $('#".$ia[1]."').val();
//    var filecount = $('#".$ia[1]."_filecount').val();
//    displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment, pos);
});

function getQueryVariable(variable, url) {
    var query = url.split("?");
    var vars = query[1].split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
        return pair[1];
        }
    }
}

$(function() {
    $('.upload').click(function(e) {
        e.preventDefault();
        var $this = $(this);
        
        var show_title   = getQueryVariable('show_title', this.href);
        var show_comment = getQueryVariable('show_comment', this.href);
        var pos          = getQueryVariable('pos', this.href);
        var fieldname    = getQueryVariable('fieldname', this.href);
        
        var horizontalPadding = 30;
        var verticalPadding = 20;
        $('#uploader').dialog('destroy'); // destroy the old modal dialog

        if ($('#uploader').length > 0)
        {

            $('iframe#uploader', parent.document).dialog({
                title: 'Upload your files',
                autoOpen: true,
                width: 984,
                height: 440,
                modal: true,
                resizable: true,
                autoResize: true,
                draggable: true,
                closeOnEscape: false,
                beforeclose: function() {
                    //TODO: copy all the stuff in button save and exit here as well
                        var pass = document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname, show_title, show_comment, pos);
                        if (pass) {
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }
                        else {
                            //TODO: don't exit. just give a prompt
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }

                },
                overlay: {
                    opacity: 0.85,
                    background: 'black'
                },
                buttons: {
                    'Save and exit': function() {
                        var pass = document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname, show_title, show_comment, pos);
                        if (pass) {
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }
                        else {
                            //TODO: don't exit. just give a prompt
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }
                    }
                }
            }).width(984 - horizontalPadding).height(440 - verticalPadding);
        }
        else
        {
            $('<iframe id=\"uploader\" name=\"uploader\" class=\"externalSite\" src=\"' + this.href + '\" />').dialog({
                title: 'Upload your files',
                autoOpen: true,
                width: 984,
                height: 440,
                modal: true,
                resizable: false,
                autoResize: true,
                draggable: false,
                closeOnEscape: false,
                beforeclose: function() {
                    alert('calling saveandexit');
                    var pass = document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname, show_title, show_comment, pos);
                    //var pass = window.frames.uploader.saveAndExit();
                    return pass;
                },
                overlay: {
                    opacity: 0.85,
                    background: 'black'
                },
                buttons: {
                    'Save and exit': function() {
                        var pass = document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname, show_title, show_comment, pos);
                        if (pass) {
                            $(this).dialog('destroy');
                            alert('destroyed dialog');
                        }
                        else {
                            $(this).dialog('destroy');
                            alert('destroyed dialog');
                        }
                    }
                }
            }).width(984 - horizontalPadding).height(440 - verticalPadding);
        }
    });
});

function isValueInArray(arr, val) {
    inArray = false;
    for (i = 0; i < arr.length; i++) {
        if (val === arr[i]) {
            inArray = true;
        }
    }

    return inArray;
}

function displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment, pos) {
    var jsonobj;
    var i;

    if (jsonstring !== '')
    {
        jsonobj = eval('(' + jsonstring + ')');
        var display = '<table width="100%"><tr><th align="center" width="20%">&nbsp;</th>';
        if (show_title)
            display += '<th align="center"><b>Title</b></th>';
        if (show_comment)
            display += '<th align="center"><b>Comment</b></th>';
        display += '<th align="center"><b>Name</b></th></tr>';
        var image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico');

        for (i = 0; i < filecount; i++)
        {
            if (pos)
            {
                if (isValueInArray(image_extensions, jsonobj[i].ext))
                    display += '<tr><td><img src="uploader.php?filegetcontents='+decodeURIComponent(jsonobj[i].filename)+'" height=100px  align="center"/></td>';
                else
                    display += '<tr><td><img src="../images/placeholder.png" height=100px  align="center"/></td>';
            }
            else
            {
                if (isValueInArray(image_extensions, jsonobj[i].ext))
                    display += '<tr><td><img src="uploader.php?filegetcontents='+decodeURIComponent(jsonobj[i].filename)+'" height=100px  align="center"/></td>';
                else
                    display += '<tr><td><img src="images/placeholder.png" height=100px  align="center"/></td>';
            }
            if (show_title)
                display += '<td>'+jsonobj[i].title+'</td>';
            if (show_comment)
                display += '<td>'+jsonobj[i].comment+'</td>';
            display +='<td>'+decodeURIComponent(jsonobj[i].name)+'</td><td>'+'<img src="images/edit.png" onclick="$(\'.upload\').click()" style="cursor:pointer"></td></tr></table>';
        }
        $('#'+fieldname+'_uploadedfiles').html(display);
    }
}

function copyJSON(jsonstring, filecount, fieldname, show_title, show_comment, pos) {
    $('#'+fieldname).val(jsonstring);
    $('#'+fieldname+'_filecount').val(filecount);
    
    displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment, pos);
}


function showBasic() {
    $('#basic').show();
}

function hideBasic() {
    $('#basic').hide();
}
