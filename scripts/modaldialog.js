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
                var pass;
                if(document.getElementById('uploader').contentDocument) {
                    if(document.getElementById('uploader').contentDocument.defaultView)
                        {       /*Firefox*/
                        pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);
                    }
                    else
                        {       /*IE8*/
                        pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                    }
                }
                else
                    {    /*IE6*/
                    pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                }
                if (pass) {
                    $(this).dialog('close');
                    $('iframe#uploader').remove();
                    $(this).dialog('destroy');
                    checkconditions();
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
                        var pass; if(document.getElementById('uploader').contentDocument) {
                            if(document.getElementById('uploader').contentDocument.defaultView)    {       /*Firefox*/
                                pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);
                            }else{       /*IE8*/
                                pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                            }
                        }else{
                            /*IE6*/    pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                        }
                        if (pass) {
                            $('iframe#uploader').remove();
                            $(this).dialog('destroy');
                            checkconditions();
                        }
                        return true;

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
                        var pass;
                        if(document.getElementById('uploader').contentDocument) {
                            if(document.getElementById('uploader').contentDocument.defaultView)
                                {       /*Firefox*/
                                pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);
                            }else{       /*IE8*/
                                pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                            }
                        }else{    /*IE6*/
                            pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                        }
                        if (pass) {
                            $('iframe#uploader').remove();
                            $(this).dialog('destroy');
                            checkconditions();
                        }
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
    var vars = url.split("/");
    for (var i=0;i<vars.length;i++) {
        //var pair = vars[i].split("=");
        if (vars[i] == variable) {
        return vars[i+1];
        }
    }
    // If not found try with ?
    // TODO : replace by a regexp
   var vars = url.replace(/\&amp;/g,'&').split("&");
   for (var i=0;i<vars.length;i++) {
           var pair = vars[i].split("=");
           if(pair[0] == variable){return pair[1];}
   }
   return null;
}

function isValueInArray(arr, val) {
    inArray = false;
    for (i = 0; i < arr.length; i++) {
        if (val.toLowerCase() == arr[i].toLowerCase()) {
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
        display = '<table width="100%" class="question uploadedfiles"><thead><tr><td width="20%">&nbsp;</td>';
        if (show_title != 0)
            display += '<th>'+translt.headTitle+'</th>';
        if (show_comment != 0)
            display += '<th>'+translt.headComment+'</th>';
        display += '<th>'+translt.headFileName+'</th></tr></thead><tbody>';
        var image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico');

        for (i = 0; i < filecount; i++)
            {
            if (pos)
                {
                if (isValueInArray(image_extensions, jsonobj[i].ext))
                    display += '<tr><td class="upload image"><img src="' + uploadurl + '/sid/'+surveyid+'/filegetcontents/'+decodeURIComponent(jsonobj[i].filename)+'" height=100px /></td>';
                else
                    display += '<tr><td class="upload placeholder"><img src="'+imageurl+'/placeholder.png" height=100px /></td>';
            }
            else
                {
                if (isValueInArray(image_extensions, jsonobj[i].ext))
                    display += '<tr><td class="upload image"><img src="' + uploadurl + 'filegetcontents/'+decodeURIComponent(jsonobj[i].filename)+'" height=100px /></td>';
                else
                    display += '<tr><td class="upload placeholder"><img src="'+imageurl+'/placeholder.png" height=100px /></td>';
            }
            if (show_title != 0)
                display += '<td class="upload title">'+jsonobj[i].title+'</td>';
            if (show_comment != 0)                                                                                                  
                display += '<td class="upload comment">'+jsonobj[i].comment+'</td>';
            display +='<td class="upload edit">'+decodeURIComponent(jsonobj[i].name)+'</td><td>'+'<img src="'+imageurl+'/edit.png" onclick="javascript:upload_'+fieldname+'();$(\'#upload_'+fieldname+'\').click();" style="cursor:pointer"></td></tr>';
        }
        display += '</tbody></table>';

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
