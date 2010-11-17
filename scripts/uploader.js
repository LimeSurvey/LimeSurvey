$(document).ready(function(){
    
    var ia = $('#ia').val();

    /* Load the previously uploaded files */
    var filecount = window.parent.window.$('#'+ia+'_filecount').val();
    $('#filecount').val(filecount);

    var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

    if (filecount > 0)
    {
        var jsontext = window.parent.window.$('#'+ia).val();
        var json = eval('(' + jsontext + ')');

        var i;
        $('#licount').val(filecount);

        for (i = 0; i <  filecount; i++)
        {
            var previewblock =  "<li id='li_"+i+"' class='previewblock'><div>"+
                    "<table align='center'><tr>"+
                        "<td  align='center' width='50%' padding='20px' >";

            if (isValueInArray(image_extensions, json[i].ext))
                previewblock += "<img src='upload/tmp/"+decodeURIComponent(json[i].name)+"' height='60px' />"+decodeURIComponent(json[i].name);
            else
                previewblock += "<img src='images/placeholder.png' height='60px' /><br />"+decodeURIComponent(json[i].name);

            previewblock += "</td>";

            if ($('#show_title').val() == 1 && $('#show_comment').val() == 1)
                previewblock += "<td align='center'><label>Title</label><br /><br /><label>Comments</label></td><td align='center'><input type='text' value='"+json[i].title+"' id='title_"+i+"' /><br /><br /><input type='text' value='"+json[i].comment+"' id='comment_"+i+"' /></td>";
            else if ($('#show_title').val() == 1)
                previewblock += "<td align='center'><label>Title</label></td><td align='center'><input type='text' value='"+json[i].title+"' id='title_"+i+"' /></td>";
            else if ($('#show_comment').val() == 1)
                previewblock += "<td align='center'><label>Comment</label></td><td align='center'><input type='text' value='"+json[i].comment+"' id='comment_"+i+"' /></td>";

            previewblock += "<td align='center' width='20%' ><img style='cursor:pointer' src='images/delete.png' onclick='deletefile("+i+")' /></td></tr></table>"+
                    "<input type='hidden' id='size_"    +i+"' value="+json[i].size+" />"+
                    "<input type='hidden' id='name_"    +i+"' value="+json[i].name+" />"+
                    "<input type='hidden' id='filename_"+i+"' value="+json[i].filename+" />"+
                    "<input type='hidden' id='ext_"     +i+"' value="+json[i].ext+"  />"+
                    "</div></li>";

            // add file to the list
            $('#listfiles').append(previewblock);
        }
    }

    // The upload button
    var button = $('#button1'), interval;

    new AjaxUpload(button, {
        action: 'upload.php?sid='+surveyid,
        name: 'uploadfile',
        data: {
            valid_extensions : $('#allowed_filetypes').val(),
            maxfilesize : $('#maxfilesize').val(),
            preview : $('#preview').val(),
            surveyid : surveyid
        },
        onSubmit : function(file, ext){

            var maxfiles = $('#maxfiles').val();
            var filecount = $('#filecount').val();
            var allowed_filetypes = $('#allowed_filetypes').val().split(",");

            /* If maximum number of allowed filetypes have already been uploaded,
             * do not upload the file and display an error message ! */
            if (filecount >= maxfiles)
            {
                $('#notice').html('<p class="error">Sorry, No more files can be uploaded !</p>');
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
                $('#notice').html('<p class="error">Sorry, Only "'+ $('#allowed_filetypes').val()+'" files can be uploaded for this question !</p>');
                return false;
            }

            // change button text, when user selects file
            button.text('Uploading');

            // If you want to allow uploading only 1 file at time,
            // you can disable upload button
            this.disable();

            // Uploding -> Uploading. -> Uploading...
            interval = window.setInterval(function(){
                var text = button.text();
                if (text.length < 13){
                    button.text(text + '.');
                } else {
                    button.text('Uploading');
                }
            }, 400);
        },
        onComplete: function(file, response){
            button.text('Select file');
            window.clearInterval(interval);
            // enable upload button
            this.enable();

            // Once the file has been uploaded via AJAX,
            // the preview is appended to the list of files
            var metadata = eval('(' + response + ')');
            $('#notice').html('<p class="success">'+metadata.msg+'</p>');
            var count = parseInt($('#licount').val());

            var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");
            
            if (metadata.success)
            {
                var previewblock =  "<li id='li_"+count+"' class='previewblock'><div>"+
                                        "<table align='center'><tr>"+
                                            "<td  align='center' width='50%'>";

                // If the file is not an image, use a placeholder
                if (isValueInArray(image_extensions, metadata.ext))
                    previewblock += "<img src='upload/tmp/"+decodeURIComponent(metadata.id)+"' height='60px' />";
                else
                    previewblock += "<img src='images/placeholder.png' height='60px' />";

                previewblock += "<br />"+decodeURIComponent(metadata.name)+"</td>";
                if ($("#show_title").val() == 1 && $("#show_comment").val() == 1)
                    previewblock += "<td align='center'><label>Title</label><br /><br /><label>Comments</label></td><td align='center'><input type='text' value='' id='title_"+count+"' /><br /><br /><input type='text' value='' id='comment_"+count+"' /></td>";
                else if ($("#show_title").val() == 1)
                    previewblock += "<td align='center'><label>Title</label></td><td align='center'><input type='text' value='' id='title_"+count+"' /></td>";
                else if ($("#show_comment").val() == 1)
                    previewblock += "<td align='center'><label>Comment</label></td><td align='center'><input type='text' value='' id='comment_"+count+"' /></td>";

                previewblock += "<td  align='center' width='20%'><img style='cursor:pointer' src='images/delete.png' onclick='deletefile("+count+")'/></td>"+
                                        "</tr></table>"+
                                        "<input type='hidden' id='size_"+count+"' value="+metadata.size+" />"+
                                        "<input type='hidden' id='file_index_"+count+"' value="+metadata.file_index+" />"+
                                        "<input type='hidden' id='name_"+count+"' value="+metadata.name+" />"+
                                        "<input type='hidden' id='filename_"+count+"' value="+metadata.filename+" />"+
                                        "<input type='hidden' id='ext_" +count+"' value="+metadata.ext+"  />"+
                                    "</div></li>";

                // add file to the list
                $('#listfiles').prepend(previewblock);
                count++;
                $('#licount').val(count);
                var filecount = $('#filecount').val();
                var minfiles = $('#minfiles').val();
                filecount++;
                var maxfiles = $('#maxfiles').val();
                $('#filecount').val(filecount);

                if (filecount < minfiles)
                    $('#uploadstatus').html('Please upload '+ (minfiles - filecount) + ' more files.');
                else if (filecount < maxfiles)
                    $('#uploadstatus').html('If you wish, you may upload '+ (maxfiles - filecount) + ' more files; else you may Save and exit');
                else
                    $('#uploadstatus').html('The maximum number of files have been uploaded. You may save and exit');

                if (filecount >= maxfiles)
                    $('#notice').html('<p class="success">Maximum number of files have been uploaded. You may Save and Exit !</p>');
            }
        }
    });

    // if it has been jst opened, the upload button should be automatically clicked !
    // TODO: auto open using click() not working at all ! :(
});

function isValueInArray(arr, val) {
    inArray = false;
    for (i = 0; i < arr.length; i++)
        if (val.toLowerCase() == arr[i].toLowerCase())
            inArray = true;

    return inArray;
}

// pass the JSON data from the iframe to the main survey page
function passJSON() {
    var json = "[";
    var filecount = 0;
    var licount = parseInt($('#licount').val());
    var i = 0;

    while (i < licount)
    {
        if (filecount > 0)
            json += ",";

        if ($("#li_"+i).is(':visible'))
        {
            json += '{';

            if ($("#show_title").val() == 1)
                json += '"title":"' +$("#title_"  +i).val()+'",';
            if ($("#show_comment").val() == 1)
                json += '"comment":"'+$("#comment_"+i).val()+'",';
            json += '"size":"'   +$("#size_"   +i).val()+'",'+
                    '"name":"'   +$("#name_"   +i).val()+'",'+
                    '"filename":"'   +$("#filename_"   +i).val()+'",'+
                    '"ext":"'    +$("#ext_"    +i).val()+'"}';

            filecount += 1;
            i += 1;
        }
        else
        {
            i += 1;
        }
    }
    json += "]";
    window.parent.window.copyJSON(json, filecount);
}

function saveAndExit() {
    var filecount = $("#filecount").val();
    var minfiles  = $("#minfiles").val();

    if (minfiles != 0 && filecount < minfiles)
    {
        var confirmans = confirm("You need to upload " + (minfiles - filecount) + " more files for this question.\n\Are you sure you want to exit ?")
        if (confirmans)
        {
            passJSON();
            return true
        }
        else
            return false;
    }
    else
    {
        passJSON();
        return true;
    }
}

function deletefile(i) {
    var xmlhttp;
    if (window.XMLHttpRequest)
        xmlhttp=new XMLHttpRequest();
    else
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            $('#notice').html('<p class="success">'+xmlhttp.responseText+'</p>');
            setTimeout(function() {
                $(".success").remove();
            }, 5000);
        }
    }
    xmlhttp.open('GET','delete.php?sid='+surveyid+'&file_index='+$("#file_index_"+i).val(),true);
    xmlhttp.send();

    $("#li_"+i).hide();
    var filecount = $('#filecount').val();
    filecount--;
    $('#filecount').val(filecount);
}