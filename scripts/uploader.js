$(document).ready(function(){
    var fieldname = $('#ia').val();

    /* Load the previously uploaded files */
    var filecount = window.parent.window.$('#'+fieldname+'_filecount').val();
    $('#'+fieldname+'_filecount').val(filecount);

    var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");

    if (filecount > 0)
    {
        var jsontext = window.parent.window.$('#'+fieldname).val();
        var json = eval('(' + jsontext + ')');

        var i;
        $('#'+fieldname+'_licount').val(filecount);

        for (i = 1; i <=  filecount; i++)
        {
            var previewblock =  "<li id='"+fieldname+"_li_"+i+"' class='previewblock'><div>"+
                    "<table align='center'><tr>"+
                       "<td  align='center' width='50%' padding='20px' >";

            if (isValueInArray(image_extensions, json[i-1].ext))
                previewblock += "<img src='uploader.php?filegetcontents="+json[i-1].filename+"' height='60px' />"+decodeURIComponent(json[i-1].name);
            else
                previewblock += "<img src='images/placeholder.png' height='60px' /><br />"+decodeURIComponent(json[i-1].name);

            previewblock += "</td>";
            if ($('#'+fieldname+'_show_title').val() == 1 && $('#'+fieldname+'_show_comment').val() == 1)
                previewblock += "<td align='center'><label>"+translt.titleFld+"</label><br /><br /><label>"+translt.commentFld+"</label></td><td align='center'><input type='text' value='"+json[i-1].title+"' id='"+fieldname+"_title_"+i+"' /><br /><br /><input type='text' value='"+json[i-1].comment+"' id='"+fieldname+"_comment_"+i+"' /></td>";
            else if ($('#'+fieldname+'_show_title').val() == 1)
                previewblock += "<td align='center'><label>"+translt.titleFld+"</label></td><td align='center'><input type='text' value='"+json[i-1].title+"' id='"+fieldname+"_title_"+i+"' /></td>";
            else if ($('#'+fieldname+'_show_comment').val() == 1)
                previewblock += "<td align='center'><label>"+translt.commentFld+"</label></td><td align='center'><input type='text' value='"+json[i-1].comment+"' id='"+fieldname+"_comment_"+i+"' /></td>";

            previewblock += "<td align='center' width='20%' ><img style='cursor:pointer' src='images/delete.png' onclick='deletefile(\""+fieldname+"\", "+i+")' /></td></tr></table>"+
                    "<input type='hidden' id='"+fieldname+"_size_"    +i+"' value="+json[i-1].size+" />"+
                    "<input type='hidden' id='"+fieldname+"_name_"    +i+"' value="+json[i-1].name+" />"+
                    "<input type='hidden' id='"+fieldname+"_file_index_"+i+"' value="+i+" />"+
                    "<input type='hidden' id='"+fieldname+"_filename_"+i+"' value="+json[i-1].filename+" />"+
                    "<input type='hidden' id='"+fieldname+"_ext_"     +i+"' value="+json[i-1].ext+"  />"+
                    "</div></li>";

            // add file to the list
            $('#'+fieldname+'_listfiles').append(previewblock);
        }
    }

    // The upload button
    var button = $('#button1'), interval;

    new AjaxUpload(button, {
        action: 'upload.php?sid='+surveyid+'&preview='+questgrppreview+'&fieldname='+fieldname,
        name: 'uploadfile',
        data: {
            valid_extensions : $('#'+fieldname+'_allowed_filetypes').val(),
            max_filesize : $('#'+fieldname+'_maxfilesize').val(),
            preview : $('#preview').val(),
            surveyid : surveyid,
            fieldname : fieldname
        },
        onSubmit : function(file, ext){

            var maxfiles = parseInt($('#'+fieldname+'_maxfiles').val());
            var filecount = parseInt($('#'+fieldname+'_filecount').val());
            var allowed_filetypes = $('#'+fieldname+'_allowed_filetypes').val().split(",");

            /* If maximum number of allowed filetypes have already been uploaded,
             * do not upload the file and display an error message ! */
            if (filecount >= maxfiles)
            {
                $('#notice').html('<p class="error">'+translt.errorNoMoreFiles+'</p>');
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
                $('#notice').html('<p class="error">'+translt.errorOnlyAllowed.replace('%s',$('#'+fieldname+'_allowed_filetypes').val())+'</p>');
                return false;
            }

            // change button text, when user selects file
            button.text(translt.uploading);

            // If you want to allow uploading only 1 file at time,
            // you can disable upload button
            this.disable();

            // Uploding -> Uploading. -> Uploading...
            interval = window.setInterval(function(){
                var text = button.text();
                if (text.length < 13){
                    button.text(text + '.');
                } else {
                    button.text(translt.uploading);
                }
            }, 400);
        },
        onComplete: function(file, response){
            button.text(translt.selectfile);
            window.clearInterval(interval);
            // enable upload button
            this.enable();

            // Once the file has been uploaded via AJAX,
            // the preview is appended to the list of files
            var metadata = eval('(' + response + ')');
            
            $('#notice').html('<p class="success">'+metadata.msg+'</p>');
            var count = parseInt($('#'+fieldname+'_licount').val());
            count++;
            $('#'+fieldname+'_licount').val(count);

            var image_extensions = new Array("gif", "jpeg", "jpg", "png", "swf", "psd", "bmp", "tiff", "jp2", "iff", "bmp", "xbm", "ico");
            
            if (metadata.success)
            {
                var previewblock =  "<li id='"+fieldname+"_li_"+count+"' class='previewblock'><div>"+
                                        "<table align='center'><tr>"+
                                            "<td  align='center' width='50%'>";

                // If the file is not an image, use a placeholder
                if (isValueInArray(image_extensions, metadata.ext))
                    previewblock += "<img src='uploader.php?filegetcontents="+decodeURIComponent(metadata.filename)+"' height='60px' />";
                else
                    previewblock += "<img src='images/placeholder.png' height='60px' />";

                previewblock += "<br />"+decodeURIComponent(metadata.name)+"</td>";
                if ($("#"+fieldname+"_show_title").val() == 1 && $("#"+fieldname+"_show_comment").val() == 1)
                    previewblock += "<td align='center'><label>"+translt.titleFld+"</label><br /><br /><label>"+translt.commentFld+"</label></td><td align='center'><input type='text' value='' id='"+fieldname+"_title_"+count+"' /><br /><br /><input type='text' value='' id='"+fieldname+"_comment_"+count+"' /></td>";
                else if ($("#"+fieldname+"_show_title").val() == 1)
                    previewblock += "<td align='center'><label>"+translt.titleFld+"</label></td><td align='center'><input type='text' value='' id='"+fieldname+"_title_"+count+"' /></td>";
                else if ($("#"+fieldname+"_show_comment").val() == 1)
                    previewblock += "<td align='center'><label>"+translt.commentFld+"</label></td><td align='center'><input type='text' value='' id='"+fieldname+"_comment_"+count+"' /></td>";

                previewblock += "<td  align='center' width='20%'><img style='cursor:pointer' src='images/delete.png' onclick='deletefile(\""+fieldname+"\", "+count+")'/></td>"+
                                        "</tr></table>"+
                                        "<input type='hidden' id='"+fieldname+"_size_"+count+"' value="+metadata.size+" />"+
                                        "<input type='hidden' id='"+fieldname+"_file_index_"+count+"' value="+metadata.file_index+" />"+
                                        "<input type='hidden' id='"+fieldname+"_name_"+count+"' value="+metadata.name+" />"+
                                        "<input type='hidden' id='"+fieldname+"_filename_"+count+"' value="+metadata.filename+" />"+
                                        "<input type='hidden' id='"+fieldname+"_ext_" +count+"' value="+metadata.ext+"  />"+
                                    "</div></li>";

                // add file to the list
                $('#'+fieldname+'_listfiles').prepend(previewblock);
                var filecount = parseInt($('#'+fieldname+'_filecount').val());
                var minfiles = parseInt($('#'+fieldname+'_minfiles').val());
                filecount++;
                var maxfiles = parseInt($('#'+fieldname+'_maxfiles').val());
                $('#'+fieldname+'_filecount').val(filecount);
                
                if (filecount < minfiles)
                    $('#uploadstatus').html(translt.errorNeedMore.replace('%s',(minfiles - filecount)));
                else if (filecount < maxfiles)
                    $('#uploadstatus').html(translt.errorMoreAllowed.replace('%s',(maxfiles - filecount)));
                else
                    $('#uploadstatus').html(translt.errorMaxReached);
                
                if (filecount >= maxfiles)
                    $('#notice').html('<p class="success">'+translt.errorTooMuch+'</p>');
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
function passJSON(fieldname, show_title, show_comment, pos) {
    var json = "[";
    var filecount = 0;
    var licount   = parseInt($('#'+fieldname+'_licount').val());
    var i = 0;

    while (i <= licount)
    {
        if (filecount > 0)
            json += ",";

        if ($("#"+fieldname+"_li_"+i).is(':visible'))
        {
            json += '{';

            if ($("#"+fieldname+"_show_title").val() == 1)
                json += '"title":"' +$("#"+fieldname+"_title_"  +i).val()+'",';
            if ($("#"+fieldname+"_show_comment").val() == 1)
                json += '"comment":"'+$("#"+fieldname+"_comment_"+i).val()+'",';
            json += '"size":"'   +$("#"+fieldname+"_size_"   +i).val()+'",'+
                    '"name":"'   +$("#"+fieldname+"_name_"   +i).val()+'",'+
                    '"filename":"'   +$("#"+fieldname+"_filename_"   +i).val()+'",'+
                    '"ext":"'    +$("#"+fieldname+"_ext_"    +i).val()+'"}';

            filecount += 1;
            i += 1;
        }
        else
        {
            i += 1;
        }
    }
    json += "]";
    window.parent.window.copyJSON(json, filecount, fieldname, show_title, show_comment, pos);
}
function saveAndExit(fieldname, show_title, show_comment, pos) {
    var filecount = parseInt($('#'+fieldname+'_filecount').val());
    var minfiles  = parseInt($('#'+fieldname+'_minfiles').val());

    if (minfiles != 0 && filecount < minfiles)
    {
        var confirmans = confirm(translt.errorNeedMore.replace('%s', (minfiles - filecount)))
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
    var xmlhttp;
    if (window.XMLHttpRequest)
        xmlhttp=new XMLHttpRequest();
    else
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    
    var filecount = parseInt($('#'+fieldname+'_filecount').val());
    var licount   = parseInt($('#'+fieldname+'_licount').val());

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            $('#notice').html('<p class="success">'+xmlhttp.responseText+'</p>');
            setTimeout(function() {
                $(".success").remove();
            }, 5000);

            $("#"+fieldname+"_li_"+count).hide();
            filecount--;
            $('#'+fieldname+'_filecount').val(filecount);

            // rearrange the file indexes
            // i.e move the files below i to one step up

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
                $('#uploadstatus').html(translt.errorNeedMore.replace('%s',(minfiles - filecount)));
            else
                $('#uploadstatus').html(translt.errorMoreAllowed.replace('%s',(maxfiles - filecount)));
        }
    }
    file_index = $("#"+fieldname+"_file_index_"+count).val();
    xmlhttp.open('GET','delete.php?sid='+surveyid+'&fieldname='+fieldname+'&file_index='+file_index, true);
    xmlhttp.send();
}