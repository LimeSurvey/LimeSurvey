$(document).ready(function(){
    doFileUpload();
    fixParentHeigth();
});

function fixParentHeigth()
{
    if(window != top)
    {
        frameheight=Math.max($('html').outerHeight()+30,150);
        $(parent.jQuery.find('#uploader')).css("height",frameheight+"px");
        if(jQuery.isFunction(parent.resetUploaderPosition))
            parent.resetUploaderPosition();
    }
}
function doFileUpload(){
    var fieldname = $('#ia').val();

    /* Load the previously uploaded files */
    var filecount = window.parent.window.$('#'+fieldname+'_filecount').val();
    $('#'+fieldname+'_filecount').val(filecount);

    var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

    if (filecount > 0)
    {
        var jsontext = window.parent.window.$('#'+fieldname).val();
        var json = eval('(' + jsontext + ')');
        if( $('#field'+fieldname+'_listfiles').length==0){
            $("<ul id='field"+fieldname+"_listfiles' class='files-list' />").insertAfter("#uploadstatus");
        }
        var i;
        $('#'+fieldname+'_licount').val(filecount);

        for (i = 1; i <=  filecount; i++)
        {
            var previewblock =  "<li id='"+fieldname+"_li_"+i+"' class='previewblock file-element'>";
            previewblock +="<div class='file-preview'>";
            if (isValueInArray(image_extensions, json[i-1].ext.toLowerCase()))
                previewblock += "<img src='"+uploadurl+"/filegetcontents/"+json[i-1].filename+"' onload='fixParentHeigth()' class='uploaded' />";
            else
                previewblock += "<div class='upload-placeholder' />";
            previewblock += "<span class='file-name'>"+decodeURIComponent(json[i-1].name)+"</span>";
            previewblock += "</div>";
            
            if ($('#'+fieldname+'_show_title').val() == 1 || $('#'+fieldname+'_show_comment').val() == 1)
            {
                previewblock +="<div class='file-info'><fieldset>";
                if($('#'+fieldname+'_show_title').val() == 1)
                    previewblock += "<label for='"+fieldname+"_title_"+i+"'>"+uploadLang.titleFld+"</label>"+"<div class='input-container'><input type='text' value='"+escapeHtml(json[i-1].title)+"' id='"+fieldname+"_title_"+i+"' /></div>";
                if($('#'+fieldname+'_show_comment').val() == 1)
                    previewblock += "<label for='"+fieldname+"_comment_"+i+"'>"+uploadLang.commentFld+"</label>"+"<div class='input-container'><input type='text' value='"+escapeHtml(json[i-1].comment)+"' id='"+fieldname+"_comment_"+i+"' /></div>";
                previewblock += "</fieldset></div>";
            }

            previewblock +="<div class='file-tool'>";
            previewblock += "<a class='delete-file' onclick='deletefile(\""+fieldname+"\", "+i+")'>"+uploadLang.deletefile+"</a>";
            previewblock += "</div>";

            previewblock += "<input type='hidden' id='"+fieldname+"_size_"    +i+"' value="+json[i-1].size+" />"+
                    "<input type='hidden' id='"+fieldname+"_name_"    +i+"' value="+json[i-1].name+" />"+
                    "<input type='hidden' id='"+fieldname+"_file_index_"+i+"' value="+i+" />"+
                    "<input type='hidden' id='"+fieldname+"_filename_"+i+"' value="+json[i-1].filename+" />"+
                    "<input type='hidden' id='"+fieldname+"_ext_"     +i+"' value="+json[i-1].ext+"  />";

            previewblock += "</li>";

            // add file to the list
            $('#field'+fieldname+'_listfiles').append(previewblock);
            fixParentHeigth();
        }
    }

    // The upload button
    var button = $('#button1'), interval;

    new AjaxUpload(button, {
        action: uploadurl + '/sid/'+surveyid+'/preview/'+questgrppreview+'/fieldname/'+fieldname+'/',
        name: 'uploadfile',
        data: {
            valid_extensions : $('#'+fieldname+'_allowed_filetypes').val(),
            max_filesize : $('#'+fieldname+'_maxfilesize').val(),
            preview : $('#preview').val(),
            surveyid : surveyid,
            fieldname : fieldname,
            YII_CSRF_TOKEN : csrfToken
        },
        onSubmit : function(file, ext){

            var maxfiles = parseInt($('#'+fieldname+'_maxfiles').val());
            var filecount = parseInt($('#'+fieldname+'_filecount').val());
            var allowed_filetypes = $('#'+fieldname+'_allowed_filetypes').val().split(",");

            /* If maximum number of allowed filetypes have already been uploaded,
             * do not upload the file and display an error message ! */
            if (filecount >= maxfiles)
            {
                $('#notice').html('<p class="error">'+uploadLang.errorNoMoreFiles+'</p>');
                fixParentHeigth();
                return false;
            }

            /* If the file being uploaded is not allowed,
             * do not upload the file and display an error message ! */
            var allowSubmit = false;
            for (var i = 0; i < allowed_filetypes.length; i++)
            {
                //check to see if it's the proper extension
                if (jQuery.trim(allowed_filetypes[i].toLowerCase()) == jQuery.trim(ext.toLowerCase()) )
                {
                    //it's the proper extension
                    allowSubmit = true;
                    break;
                }
            }
            if (allowSubmit == false)
            {
                $('#notice').html('<p class="error">'+uploadLang.errorOnlyAllowed.replace('%s',$('#'+fieldname+'_allowed_filetypes').val())+'</p>');
                fixParentHeigth();
                return false;
            }

            // change button text, when user selects file
            button.text(uploadLang.uploading);

            // If you want to allow uploading only 1 file at time,
            // you can disable upload button
            this.disable();

            // Uploding -> Uploading. -> Uploading...
            interval = window.setInterval(function(){
                var text = button.text();
                if (text.length < 13){
                    button.text(text + '.');
                } else {
                    button.text(uploadLang.uploading);
                }
            }, 400);
        },
        onComplete: function(file, response){
            button.text(uploadLang.selectfile);
            window.clearInterval(interval);
            // enable upload button
            this.enable();

            // Once the file has been uploaded via AJAX,
            // the preview is appended to the list of files
            var metadata = eval('(' + response + ')');

            var count = parseInt($('#'+fieldname+'_licount').val());
            count++;
            $('#'+fieldname+'_licount').val(count);

            var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

            if (metadata.success)
            {
                $('#notice').html('<p class="success">'+metadata.msg+'</p>');
                if( $('#field'+fieldname+'_listfiles').length==0){
                    $("<ul id='field"+fieldname+"_listfiles' class='files-list' />").insertAfter("#uploadstatus");
                }
                var previewblock =  "<li id='"+fieldname+"_li_"+count+"' class='previewblock file-element'>";

                previewblock +="<div class='file-preview'>";
                if (isValueInArray(image_extensions, metadata.ext.toLowerCase()))
                    previewblock += "<img src='"+uploadurl+"/filegetcontents/"+decodeURIComponent(metadata.filename)+"' onload='fixParentHeigth()' class='uploaded' />";
                else
                    previewblock += "<div class='upload-placeholder' />";
                previewblock += "<span class='file-name'>"+decodeURIComponent(metadata.name)+"<span>";
                previewblock += "</div>";

                if ($('#'+fieldname+'_show_title').val() == 1 || $('#'+fieldname+'_show_comment').val() == 1)
                {
                    previewblock +="<div class='file-info'><fieldset>";
                    if($('#'+fieldname+'_show_title').val() == 1)
                        previewblock += "<label for='"+fieldname+"_title_"+count+"'>"+uploadLang.titleFld+"</label>"+"<div class='input-container'><input type='text' value='' id='"+fieldname+"_title_"+count+"' /></div>";
                    if($('#'+fieldname+'_show_comment').val() == 1)
                        previewblock += "<label for='"+fieldname+"_comment_"+count+"'>"+uploadLang.commentFld+"</label>"+"<div class='input-container'><input type='text' value='' id='"+fieldname+"_comment_"+count+"' /></div>";
                    previewblock += "</fieldset></div>";
                }

                previewblock +="<div class='file-tool'>";
                previewblock += "<a class='delete-file' onclick='deletefile(\""+fieldname+"\", "+count+")'>"+uploadLang.deletefile+"</a>";
                previewblock += "</div>";

                previewblock += "<input type='hidden' id='"+fieldname+"_size_"+count+"' value="+metadata.size+" />"+
                                "<input type='hidden' id='"+fieldname+"_file_index_"+count+"' value="+metadata.file_index+" />"+
                                "<input type='hidden' id='"+fieldname+"_name_"+count+"' value="+metadata.name+" />"+
                                "<input type='hidden' id='"+fieldname+"_filename_"+count+"' value="+metadata.filename+" />"+
                                "<input type='hidden' id='"+fieldname+"_ext_" +count+"' value="+metadata.ext+"  />";

                previewblock += "</li>";

                // add file to the list
                $('#field'+fieldname+'_listfiles').prepend(previewblock);
                var filecount = parseInt($('#'+fieldname+'_filecount').val());
                var minfiles = parseInt($('#'+fieldname+'_minfiles').val());
                filecount++;
                var maxfiles = parseInt($('#'+fieldname+'_maxfiles').val());
                $('#'+fieldname+'_filecount').val(filecount);

                if (filecount < minfiles)
                    $('#uploadstatus').html(uploadLang.errorNeedMore.replace('%s',(minfiles - filecount)));
                else if (filecount < maxfiles)
                    $('#uploadstatus').html(uploadLang.errorMoreAllowed.replace('%s',(maxfiles - filecount)));
                else
                    $('#uploadstatus').html(uploadLang.errorMaxReached);

                if (filecount >= maxfiles)
                    $('#notice').html('<p class="success">'+uploadLang.errorTooMuch+'</p>');
                fixParentHeigth();
            }
            else
            {
                $('#notice').html('<p class="error">'+metadata.msg+'</p>');
                fixParentHeigth();
            }
            
        }
    });

    // if it has been jst opened, the upload button should be automatically clicked !
    // TODO: auto open using click() not working at all ! :(
}

function isValueInArray(arr, val) {
    inArray = false;
    for (i = 0; i < arr.length; i++)
        if (val.toLowerCase() == arr[i].toLowerCase())
            inArray = true;

    return inArray;
}

// pass the JSON data from the iframe to the main survey page
function passJSON(fieldname, show_title, show_comment, pos) {
    var json = "[";
    var filecount = 0;
    var licount   = parseInt($('#'+fieldname+'_licount').val());
    var i = 1;
    while (i <= licount)
    {

        if ($("#"+fieldname+"_li_"+i).is(':visible'))
        {
            if (filecount > 0)
                json += ",";
            json += '{ ';

            if ($("#"+fieldname+"_show_title").val() == 1)
                json += '"title":"' +$("#"+fieldname+"_title_"  +i).val().replace(/"/g, '\\"')+'",';
            if ($("#"+fieldname+"_show_comment").val() == 1)
                json += '"comment":"'+$("#"+fieldname+"_comment_"+i).val().replace(/"/g, '\\"')+'",';
            json += '"size":"'   +$("#"+fieldname+"_size_"   +i).val()+'",'+
                    '"name":"'   +$("#"+fieldname+"_name_"   +i).val()+'",'+
                    '"filename":"'   +$("#"+fieldname+"_filename_"   +i).val()+'",'+
                    '"ext":"'    +$("#"+fieldname+"_ext_"    +i).val()+'"}';

            filecount += 1;
        }
        i += 1;
    }
    json += "]";
    window.parent.window.copyJSON(json, filecount, fieldname, show_title, show_comment, pos);
}
function saveAndExit(fieldname, show_title, show_comment, pos) {
    var filecount = parseInt($('#'+fieldname+'_filecount').val());
    var minfiles  = parseInt($('#'+fieldname+'_minfiles').val());

    if (minfiles != 0 && filecount < minfiles && showpopups)
    {
        var confirmans = confirm(uploadLang.errorNeedMoreConfirm.replace('%s', (minfiles - filecount)))
        if (confirmans)
        {
            passJSON(fieldname, show_title, show_comment, pos);
            return true
        }
        else
            return false;
    }
    else
    {
        passJSON(fieldname, show_title, show_comment, pos);
        return true;
    }
}

function deletefile(fieldname, count) {

    var file_index;
    var filename=$("#"+fieldname+"_filename_"+count).val();
    var name=$("#"+fieldname+"_name_"+count).val();

    var filecount = parseInt($('#'+fieldname+'_filecount').val());
    var licount   = parseInt($('#'+fieldname+'_licount').val());
    
    $.ajax(
    {
        method: "POST",
        url: uploadurl,
        data: { 
            'delete': 1, 
            'fieldname': fieldname,
            'filename' : filename,
            'name' : name,
            YII_CSRF_TOKEN : csrfToken
            }
    })
    .done(function( msg )
    {
        $('#notice').html('<p class="success">'+msg+'</p>');
        setTimeout(function() {
            $(".success").remove();
        }, 5000);
        $("#"+fieldname+"_li_"+count).hide();
        filecount--;
        $('#'+fieldname+'_filecount').val(filecount);
        file_index = $("#"+fieldname+"_file_index_"+count).val();
        for (j = count; j <= licount; j++)
        {
            if ($('#'+fieldname+'_li_'+j).is(":visible"))
            {
                $('#'+fieldname+'_file_index_'+j).val(file_index);
                file_index++;
            }
        }
        var minfiles = parseInt($('#'+fieldname+'_minfiles').val());
        var maxfiles = parseInt($('#'+fieldname+'_maxfiles').val());

        if (filecount < minfiles)
            $('#uploadstatus').html(uploadLang.errorNeedMore.replace('%s',(minfiles - filecount)));
        else
            $('#uploadstatus').html(uploadLang.errorMoreAllowed.replace('%s',(maxfiles - filecount)));
        fixParentHeigth();
    });
}


function escapeHtml(unsafe) {
  return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}
