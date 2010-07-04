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

		div.button {
			height: 29px;
			width: 133px;
			background: url(images/button.png) 0 0;
            margin: 50px auto;
			font-size: 14px; color: #C7D92C; text-align: center; padding-top: 15px;
		}
		/*
		We can't use ":hover" preudo-class because we have
		invisible file input above, so we have to simulate
		hover effect with JavaScript.
		 */
		div.button.hover {
			background: url(images/button.png) 0 56px;
			color: #95A226;
		}

        .notice {
            background: #FFF6BF;
            border: 2px solid #FFD324;
            text-align: center;
            margin: 10px auto;
            padding: 5px 20px;
            color: red;
        }

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

	</style>

 	<script type="text/javascript">
        $(document).ready(function(){
            
            var ia = $('#ia').val();

            $('#saveandexit').click(function() {
                passJSON();
            });

            /* Load the previously uploaded files */
            var filecount = window.parent.window.$('#'+ia+'_filecount').val();
            $('#filecount').val(filecount);

            if (filecount > 0)
            {
                var jsontext = window.parent.window.$('#'+ia).val();
                var json = eval('(' + jsontext + ')');

                var i;
                $('#licount').val(filecount);

                for (i = 0; i <  filecount; i++)
                {
                    var previewblock =  "<li id='li_"+i+"'><div>"+
                            "<table align='center'><tr>"+
                                "<td  align='center' width='50%'><img src='upload/tmp/"+json[i].name+"' height='100px' /></td>"+
                                "<td align='center'><label>Title</label><br /><br /><label>Comments</label></td>"+
                                "<td align='center'><input type='text' value='"+json[i].title+"' id='title_"+i+"' /><br /><br />"+
                                "<input type='text' value='"+json[i].comment+"' id='comment_"+i+"' /></td>"+
                                /* If the file is not an image, use a placeholder */
                                "<td  align='center' width='20%'><img src='images/trash.png' onclick='deletefile("+i+")' /></td>"+
                            "</tr></table>"+
                            "<input type='hidden' id='size_"+i+"' value="+json[i].size+" />"+
                            "<input type='hidden' id='name_"+i+"' value="+json[i].name+" />"+
                            "<input type='hidden' id='ext_" +i+"' value="+json[i].ext+"  />"+
                        "</div></li>";

                    // add file to the list
                    $('#listfiles').append(previewblock);
                }
            }

            // The upload button
            var button = $('#button1'), interval;
            
            var AJAXUploadbutton = new AjaxUpload(button, {
                action: 'upload.php',
                name: 'uploadfile',
                onSubmit : function(file, ext){

                    var maxfiles = $('#maxfiles').val();
                    var filecount = $('#filecount').val();

                    if (filecount >= maxfiles)
                    {
                        $('body').prepend('<div class="notice">Sorry, No more files can be uploaded !</div>');
                        setTimeout(function() {
                            $(".notice").remove();
                        }, 10000);
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
                    }, 200);
                },
                onComplete: function(file, response){
                    button.text('Upload');
                    window.clearInterval(interval);
                    // enable upload button
                    this.enable();

                    // Once the file has been uploaded via AJAX,
                    // the preview is appended to the list of files
                    var metadata = eval('(' + response + ')');
                    $('body').prepend('<div class="notice">'+metadata.msg+'</div>');
                    setTimeout(function() {
                        $(".notice").remove();
                    }, 2000);
                    var count = parseInt($('#licount').val());
                    

                    if (metadata.success)
                    {
                        var previewblock =  "<li id='li_"+count+"' class='previewblock'><div>"+
                                                "<table align='center'><tr>"+
                                                    /* TODO: If the file is not an image, use a placeholder */
                                                    "<td  align='center' width='50%'><img src='upload/tmp/"+file+"' width='100px' /></td>"+
                                                    "<td align='center'><label>Title</label><br /><br /><label>Comments</label></td>"+
                                                    "<td align='center'><input type='text' value='' id='title_"+count+"' /><br /><br />"+
                                                    "<input type='text' value='' id='comment_"+count+"' /></td>"+
                                                    "<td  align='center' width='20%'><img src='images/trash.png' onclick='deletefile("+count+")'/></td>"+
                                                "</tr></table>"+
                                                "<input type='hidden' id='size_"+count+"' value="+metadata.size+" />"+
                                                "<input type='hidden' id='name_"+count+"' value="+metadata.name+" />"+
                                                "<input type='hidden' id='ext_" +count+"' value="+metadata.ext+"  />"+
                                            "</div></li>";

                        // add file to the list
                        $('#listfiles').prepend(previewblock);
                        count++;
                        $('#licount').val(count);
                        var filecount = $('#filecount').val();
                        filecount++;
                        $('#filecount').val(filecount);
                        var maxfiles = $('#maxfiles').val();
                        if (filecount >= maxfiles) {
                            $('body').prepend('<div class="notice">Maximum number of files have been uploaded<br />You may Save and Exit !</div>');
                            setTimeout(function() {
                                $(".notice").remove();
                            }, 2000);
                        }
                    }
                }
            });
        });

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

                if (!$("#li_"+i).is(":visible"))
                {
                    i += 1;
                }
                else
                {
                    json += '{"title":"' +$("#title_"  +i).val()+'",'+
                            '"comment":"'+$("#comment_"+i).val()+'",'+
                            '"size":"'   +$("#size_"   +i).val()+'",'+
                            '"name":"'   +$("#name_"   +i).val()+'",'+
                            '"ext":"'    +$("#ext_"    +i).val()+'"}';

                    filecount += 1;
                    i += 1;
                }
            }
            json += "]";
            window.parent.window.copyJSON(json, filecount);
        }

        function deletefile(i) {
        	if (window.XMLHttpRequest)
                xmlhttp=new XMLHttpRequest();
            else
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

            xmlhttp.onreadystatechange=function()
            {
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    addNotice(xmlhttp.responseText);
            }
            xmlhttp.open('GET','delete.php?file='+$("#name_"+i).val(),true);
            xmlhttp.send();
            
            $("#li_"+i).hide();
            var filecount = $('#filecount').val();
            filecount--;
            $('#filecount').val(filecount);
        }

        function addNotice(notice) {
            $('body').prepend('<div class="notice">'+notice+'</div>');
            setTimeout(function() {
                $(".notice").remove();
            }, 2000);
        }
        
    </script>

    </head>

    <body>
        <input type="hidden" id="ia"                value="<?php echo $_GET['ia']                 ?>" />
        <input type="hidden" id="minfiles"          value="<?php echo $_GET['minfiles']           ?>" />
        <input type="hidden" id="maxfiles"          value="<?php echo $_GET['maxfiles']           ?>" />
        <input type="hidden" id="maxfilesize"       value="<?php echo $_GET['maxfilesize']        ?>" />
        <input type="hidden" id="allowed_filetypes" value="<?php echo $_GET['allowed_filetypes']  ?>" />
        <input type="hidden" id="licount"           value="0" />
        <input type="hidden" id="filecount"         value="0" />

        <!-- The upload button -->
        <div id="button1" class="button" align="center">Upload</div>

        <!-- The list of uploaded files -->
        <ul id="listfiles"></ul>

        <div  id="saveandexit" class="button" align="center">Save and Exit</div>

    </body>
</html>