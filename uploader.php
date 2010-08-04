<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Advanced File Uploader</title>
  
        <script type="text/javascript" src="scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="scripts/ajaxupload.js"></script>

        <link type="text/css" href="scripts/jquery/css/jquery-ui-1.8.1.custom.css" rel="stylesheet" />
        <script type="text/javascript" src="scripts/jquery/jquery-ui.js"></script>
        
    <style type="text/css">
        
        body {font-family: verdana, arial, helvetica, sans-serif;font-size: 12px;color: black; direction: ltr;}
        h1 {color: #C7D92C;	font-size: 18px; font-weight: 400;}
        a {	color: white;}
        a:hover, a.hover {color: #C7D92C;}
        ul { list-style: none; }
        #dialog-confirm { padding: 0 20px; float: left; width: 750px; }

        .previewblock {
            background-color: #99CCFF;
            margin: 10px auto;
            padding: 5px 20px;
        }

        img {
            display: block;
            margin: 5px;
            border: 0 none;
            border-style: solid;
        }

        .uploadmsg {
            text-align: center;
        }

        .uploadstatus {
            text-align: center;
        }

        .upload-button {
            border: 1px solid #79B7E7;
            background: url("../images/ui-bg_glass_85_dfeffc_1x400.png") repeat-x scroll 50% 50% #DFEFFC;
            color: #5670A1;
            font-weight: bold;
            outline: medium none;
            -moz-border-radius: 5px 5px 5px 5px;
            font-family: Verdana,Arial,sans-serif;
            font-size: 1em;
            float: none;
            line-height: 1.4em;
            margin: 0.5em 0.4em 0.5em 0;
            overflow: visible;
            padding: 0.6em 0.6em 0.6em 0.6em;
            width: 100px;
            text-align: center;
        }
        
        .success, .error {
            border: 1px solid;
            margin: 15px 200px 20px 200px;
            padding:15px 50px 15px 50px;
            background-repeat: no-repeat;
            background-position: 20px center;
        }

        .success {
            color: #4F8A10;
            background-color: #DFF2BF;
            background-image:url('images/success_notice.png');
            text-indent: 1.5em;
        }
        
        .error {
            color: #D8000C;
            background-color: #FFBABA;
            background-image: url('images/error_notice.png');
            text-indent: 1.5em;
        }
	</style>

 	<script type="text/javascript">
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
                action: 'upload.php',
                name: 'uploadfile',
                data: {
                    valid_extensions : $('#allowed_filetypes').val(),
                    maxfilesize : $('#maxfilesize').val(),
                    preview : $('#preview').val()
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
                            previewblock += "<img src='upload/tmp/"+decodeURIComponent(metadata.name)+"' height='60px' />";
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

                        if (filecount >= maxfiles)
                            $('#notice').html('<p class="success">Maximum number of files have been uploaded. You may Save and Exit !</p>');
                    }
                }
            });
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
                    return true
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
        	if (window.XMLHttpRequest)
                var xmlhttp=new XMLHttpRequest();
            else
                var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

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
            xmlhttp.open('GET','delete.php?file='+$("#name_"+i).val(),true);
            xmlhttp.send();
            
            $("#li_"+i).hide();
            var filecount = $('#filecount').val();
            filecount--;
            $('#filecount').val(filecount);
        }
        
    </script>

    </head>

    <body>
        <div id="notice"></div>
        <input type="hidden" id="ia"                value="<?php echo $_GET['ia']                 ?>" />
        <input type="hidden" id="minfiles"          value="<?php echo $_GET['minfiles']           ?>" />
        <input type="hidden" id="maxfiles"          value="<?php echo $_GET['maxfiles']           ?>" />
        <input type="hidden" id="maxfilesize"       value="<?php echo $_GET['maxfilesize']        ?>" />
        <input type="hidden" id="allowed_filetypes" value="<?php echo $_GET['allowed_filetypes']  ?>" />
        <input type="hidden" id="preview"           value="<?php echo $_GET['preview']            ?>" />
        <input type="hidden" id="show_comment"      value="<?php echo $_GET['show_comment']       ?>" />
        <input type="hidden" id="show_title"        value="<?php echo $_GET['show_title']         ?>" />
        <input type="hidden" id="licount"           value="0" />
        <input type="hidden" id="filecount"         value="0" />

        <!-- The upload button -->
        <div align="center" class="upload-div">
            <button id="button1" class="upload-button" type="button" >Select file</button>
        </div>
        
        <p class="uploadmsg">You can upload <?php echo $_GET['allowed_filetypes']; ?> under <?php echo $_GET['maxfilesize']; ?> KB each</p>
        <div class="uploadstatus" id="uploadstatus"></div>

        <!-- The list of uploaded files -->
        <ul id="listfiles"></ul>

    </body>
</html>