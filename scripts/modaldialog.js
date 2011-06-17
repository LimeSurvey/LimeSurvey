$(document).ready(function() {
//    $('#basic').hide();
//    var jsonstring = $('#".$ia[1]."').val();
//    var filecount = $('#".$ia[1]."_filecount').val();
//    displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment, pos);

$(function() {
    $('.upload').click(function(e) {
        e.preventDefault();
        var $this = $(this);

        var show_title   = getQueryVariable('show_title', this.href);
        var show_comment = getQueryVariable('show_comment', this.href);
        var pos          = getQueryVariable('pos', this.href);
        var fieldname    = getQueryVariable('fieldname', this.href);
        var buttonsOpts = {};
        buttonsOpts[translt.returnTxt] = function() {
                        // Fix for the IE bug 04965
                        var pass; if(document.getElementById('uploader').contentDocument) {    if(document.getElementById('uploader').contentDocument.defaultView)    {       /*Firefox*/     pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);    }else{       /*IE8*/       pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);    } }else{    /*IE6*/    pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos); } 
                        if (pass) {
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }
                    };
        
        var horizontalPadding = 30;
        var verticalPadding = 20;
        $('#uploader').dialog('destroy'); // destroy the old modal dialog

        if ($('#uploader').length > 0)
        {

            $('iframe#uploader', parent.document).dialog({
                title: translt.title,
                autoOpen: true,
                width: 984,
                height: 440,
                modal: true,
                resizable: true,
                autoResize: true,
                draggable: true,
                closeOnEscape: false,
                beforeclose: function() {
                        var pass; if(document.getElementById('uploader').contentDocument) {    if(document.getElementById('uploader').contentDocument.defaultView)    {       /*Firefox*/     pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);    }else{       /*IE8*/       pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);    } }else{    /*IE6*/    pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos); } 
                            if (pass) {
                                $(this).dialog('destroy');
                                $('iframe#uploader').remove();
                            }
                        else {
                            $(this).dialog('destroy');
                            $('iframe#uploader').remove();
                        }

                },
                overlay: {
                    opacity: 0.85,
                    background: 'black'
                },
                buttons: buttonsOpts
            }).width(984 - horizontalPadding).height(440 - verticalPadding);
        }
        else
        {
            $('<iframe id=\"uploader\" name=\"uploader\" class=\"externalSite\" src=\"' + this.href + '\" />').dialog({
                title: translt.title,
                autoOpen: true,
                width: 984,
                height: 440,
                modal: true,
                resizable: true,
                autoResize: true,
                draggable: true,
                closeOnEscape: false,
                beforeclose: function() {
                    var pass; if(document.getElementById('uploader').contentDocument) {    if(document.getElementById('uploader').contentDocument.defaultView)    {       /*Firefox*/     pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);    }else{       /*IE8*/       pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);    } }else{    /*IE6*/    pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos); } 
                    return pass;
                },
                overlay: {
                    opacity: 0.85,
                    background: 'black'
                },
                buttons: buttonsOpts
            }).width(984 - horizontalPadding).height(440 - verticalPadding);
        }
    });
});

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
    return null;
}

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
    var display = '';

    if (jsonstring == '[]') {
        $('#'+fieldname+'_uploadedfiles').html(display);
        return;
    }
    
    if (jsonstring !== '')
    {
        jsonobj = eval('(' + jsonstring + ')');
        display = '<table width="100%"><tr><th align="center" width="20%">&nbsp;</th>';
        if (show_title != 0)
            display += '<th align="center"><b>Title</b></th>';
        if (show_comment != 0)
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
            if (show_title != 0)
                display += '<td>'+jsonobj[i].title+'</td>';
            if (show_comment != 0)
                display += '<td>'+jsonobj[i].comment+'</td>';
            display +='<td>'+decodeURIComponent(jsonobj[i].name)+'</td><td>'+'<img src="images/edit.png" onclick="$(\'#upload_'+fieldname+'\').click()" style="cursor:pointer"></td></tr>';
        }
        display += '</table>';

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