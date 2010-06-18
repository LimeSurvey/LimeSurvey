<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Advanced File Uploader</title>

        <script type="text/javascript" src="scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="scripts/swfupload/swfupload.js"></script>
        <script type="text/javascript" src="scripts/jquery.swfupload.js"></script>

        <link type="text/css" href="scripts/jquery/css/jquery-ui-1.8.1.custom.css" rel="stylesheet" />

        <script type="text/javascript" src="scripts/jquery/jquery-ui.js"></script>
        
        <script type="text/javascript">
            
            $(function(){
                $('#swfupload-control').swfupload({
                    upload_url: "upload.php",
                    file_post_name: 'uploadfile',
                    file_size_limit : "1024",
                    file_types : "*.png",
                    file_types_description : "png files",
                    file_upload_limit : 5,
                    flash_url : "scripts/swfupload/swfupload.swf",
                    button_image_url : 'scripts/swfupload/wdp_buttons_upload_114x29.png',
                    button_width : 114,
                    button_height : 29,
                    button_placeholder : $('#button')[0],
                    debug: false
                })
                    .bind('fileQueued', function(event, file){
                        var index = file.id.split("_").pop();
                        var listitem='<li id="'+file.id+'" >'+
                            '<div class="progressbar" ><div class="progress" ></div></div>'+
                            '<p class="status" >Pending</p>'+
                            '<span class="cancel" ></span>'+
                            '<a href="#"><img src="images/trash.png" onclick="deletefile('+index+')" border="0" ></a>'+
                            '<input type="text"   id="title_'  +index+'" value='+file.name.substring(0, file.name.lastIndexOf("."))+' />'+
                            '<input type="hidden" id="size_'   +index+'" value='+file.size+' />'+
                            '<input type="hidden" id="name_'   +index+'" value='+file.name+' />'+
                            '<input type="hidden" id="ext_'    +index+'" value='+file.name.split(".").pop().toLowerCase()+' />'+
                            '<input type="text"   id="comment_'+index+'" value="Comments" />'+
                            '<em>'+file.name+'</em>'+
                            '</li>';
                        $('#log').append(listitem);
                        $('li#'+file.id+' .cancel').bind('click', function(){
                            var swfu = $.swfupload.getInstance('#swfupload-control');
                            swfu.cancelUpload(file.id);
                            $('li#'+file.id).slideUp('slow');
                        });
                        // start the upload since it's queued
                        $(this).swfupload('startUpload');
                    })
                    .bind('fileQueueError', function(event, file, errorCode, message){
                        alert('Size of the file '+file.name+' is greater than limit');
                    })
                    .bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){
                        $('#queuestatus').text('Files Selected: '+numFilesSelected+' / Queued Files: '+numFilesQueued);
                    })
                    .bind('uploadStart', function(event, file){
                        $('#log li#'+file.id).find('p.status').text('Uploading...');
                        $('#log li#'+file.id).find('span.progressvalue').text('0%');
                        $('#log li#'+file.id).find('span.cancel').hide();
                    })
                    .bind('uploadProgress', function(event, file, bytesLoaded){
                        //Show Progress
                        var percentage=Math.round((bytesLoaded/file.size)*100);
                        $('#log li#'+file.id).find('div.progress').css('width', percentage+'%');
                        $('#log li#'+file.id).find('span.progressvalue').text(percentage+'%');
                    })
                    .bind('uploadSuccess', function(event, file, serverData){
                        var item=$('#log li#'+file.id);
                        item.find('div.progress').css('width', '100%');
                        item.find('span.progressvalue').text('100%');
                        var pathtofile='upload/tmp/'+file.name;
                        item.addClass('success').find('p.status').html('<img src="'+pathtofile+'" height="50px" width="50px" />');
                    })
                    .bind('uploadComplete', function(event, file){
                        // upload has completed, try the next one in the queue
                        var item=$('#log li#'+file.id);
                        item.find('div.progress').hide();
                        item.find('div.progressbar').hide();
                        item.find('span.progressvalue').text(' ');
                        $(this).swfupload('startUpload');
                    })

            });

            function passJSON() {
                var json = "[";
                var filecount = 0;
                var i = 0;
                while ($("#size_"+i).length != 0)
                {
                    if (filecount > 0)
                        json += ",";

                    if ($("#size_"+i).val() == 0)
                    {
                        i += 1;
                    }
                    else
                    {
                        json += '{"title":"' +$("#title_"  +filecount).val()+'",'+
                                '"comment":"'+$("#comment_"+filecount).val()+'",'+
                                '"size":"'   +$("#size_"   +filecount).val()+'",'+
                                '"name":"'   +$("#name_"   +filecount).val()+'",'+
                                '"ext":"'    +$("#ext_"    +filecount).val()+'"}';

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

                xmlhttp.onreadystatechange=function() {
                    if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {
                        document.getElementById("mydiv").innerHTML=xmlhttp.responseText;
                    }
                }
                xmlhttp.open('GET','delete.php?file='+$("#name_"+i).val(),true);
                xmlhttp.send();
                $("#SWFUpload_0_"+i).hide();
                $("#size_"+i).val(0);
            }
        </script>

        <style type="text/css" >
            #swfupload-control p{ margin-top:70px; font-size:0.9em; }
            #log{ margin: 12px 50px 0px 50px; padding:0; list-style-type: none; }
            #log li{ list-style-position:inside; margin:2px; border:1px solid #ccc; padding:15px; font-size:12px;
                font-family:Arial, Helvetica, sans-serif; color:#333; background:#fff; position:relative;}
            #log li .progressbar{ border:1px solid #333; height:15px; background:#fff; }
            #log li .progress{ background:#999; width:0%; height:15px; }
            #log li p{ margin:0; line-height:18px; }
            #log li input{ padding: 5px; border: 10px; margin-left: 15px; margin-right: 15px; }
            #log li.success{ border:1px solid #339933; background:#ccf9b9; }
            #log li p img{ float: right; margin-right: 20px; margin-top: 10px; }
            #log li em{ margin-bottom: 10px; }
            #log li span.cancel{ position:absolute; top:5px; right:5px; width:20px; height:20px;
                background:url('scripts/swfupload/cancel.png') no-repeat; cursor:pointer; }
        </style>


    </head>

    <body>
        <div id="swfupload-control">
            <p align="center"><input type="button" id="button" /></p>
            <p id="queuestatus" ></p>
            <ul id="log"></ul>
        </div>
    </body>
</html>
