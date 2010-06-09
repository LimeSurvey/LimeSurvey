<html>
    <head>
        <link type="text/css" href="scripts/jquery/css/jquery-ui-1.8.1.custom.css" rel="stylesheet" />

        <script type="text/javascript" src="scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="scripts/jquery/jquery-ui.js"></script>
        
        <script type="text/javascript">

            $(function() {
                $("#tabs").tabs();
            });

            $(document).ready(function(){
                $('#f1_result').hide();
                $('#f1_upload_process').hide();
                $("#tabs-3 tr").hide();
            });

            $(function() {
                $("#savechanges").click(function() {
                    var filecount = $("#filecount").val();
                    var jsonstr = '[';
                    var i;
                    for (i = 0; i < filecount; i++)
                    {
                        if (i != 0)
                            jsonstr += ',';
                        jsonstr += '{"title":"'+$("#gallery_title_"+i).val()+'",';
                        jsonstr += '"comment":"'+$("#gallery_comment_"+i).val()+'",';
                        jsonstr += '"filename":"'+$("#gallery_image_"+i).attr("src").replace(/\\/g,'/').replace(  /.*\//, '' )+'"}';
                    }
                    jsonstr += ']';
                    $("#json").val(jsonstr);
                });
            });

            function startUpload(){
                $('#f1_result').hide();
                $('#f1_upload_form').hide();
                $('#f1_upload_process').show();
                return true;
            }

            function stopUpload(success, filecount, json, ia, maxfiles){
                var result = '';
                if (success === 1){
                    result = '<span>The file was uploaded successfully!</span><br/>\n\
                        You may preview/edit or delete uploaded files in the gallery tab<br/>';
                    $("#filecount").val(filecount);
                    $("#json").val(JSON.stringify(json));
                    $("input.uploadform").val('');
                    var i;
                    for (i = 0; i < json.length; i++)
                    {
                        $("#gallery_title_"+i).val(json[i].title);
                        $("#gallery_comment_"+i).val(json[i].comment);
                        //TODO-FUQT : if image, then display the image, else display a placeholder for that filetype
                        $("#gallery_image_"+i).attr("src", "upload/tmp/"+json[i].filename);
                    }
                    for (i = 0; i < 4*json.length; i++)
                        $("#tabs-3 tr:eq("+i+")").show();
                    for (i = 4*json.length; i < 4*maxfiles; i++)
                        $("#tabs-3 tr:eq("+i+")").hide();
                }
                else {
                     result = '<span>There was an error during file upload!</span><br/><br/>';
                }
                $('#f1_result').html(result);
                $('#f1_result').show();
                $('#f1_upload_process').hide();
                $('#f1_upload_form').show();

                  return true;
            }

            function passJSON() {
                var jsonstring = $('#json').val();
                var filecount  = $('#filecount').val();
                window.parent.window.copyJSON(jsonstring, filecount);
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
                var filename = $("#gallery_image_"+i).attr('src');
                xmlhttp.open('GET','delete.php?file="'+filename+'"',true);
                xmlhttp.send();

                var json = $("#json").val();

                var jsonObj = eval('('+json+')');
                jsonObj.splice(i, 1);
                json = JSON.stringify(jsonObj);
                $("#json").val(json);
                $("#filecount").val(jsonObj.length);

                var i;
                for (i = 0; i < jsonObj.length; i++)
                {
                    $("#gallery_title_"+i).val(jsonObj[i].title);
                    $("#gallery_comment_"+i).val(jsonObj[i].comment);
                    //TODO-FUQT : if image, then display the image, else display a placeholder for that filetype
                    $("#gallery_image_"+i).attr("src", "upload/tmp/"+jsonObj[i].filename);
                }
                var maxfiles = $("#maxfiles").val();
                for (i = 0; i < 4*jsonObj.length; i++)
                    $("#tabs-3 tr:eq("+i+")").show();
                for (i = 4*jsonObj.length; i < 4*maxfiles; i++)
                    $("#tabs-3 tr:eq("+i+")").hide();
            }


        </script>

    </head>

    <body style="font-size: x-small">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">From Computer</a></li>
                <li><a href="#tabs-2">From URL</a></li>
                <li><a href="#tabs-3">Gallery</a></li>
            </ul>


            <!-- From Computer Tab -->
            <div id="tabs-1">

                <form action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
                    <div id="f1_result" align="center"></div>
                    <div id="f1_upload_process" align="center">Loading...<br/><img src="images/loader.gif" align="center" alt="Loading..."/><br/></div>
                    <div id="f1_upload_form" align="center"><br/>
                        <table border="0" cellpadding="10" cellspacing="10" align="center" width="100%">
                            <tr>
                                <th align="center"><b>Title</b></th>
                                <th align="center"><b>Comment</b></th>
                                <th align="center"><b>Select file</b></th>
                            </tr>
                            <tbody>

                                <?php
                                    $maxfiles = $_GET['maxfiles'];
                                    $ia = $_GET['ia'];

                                    for ($i = 1; $i <= $maxfiles; $i++) {
                                            $output='<tr>
                                                        <td align="center"><input class="uploadform" type="text" name="title_'.$i  .'" id="title_'.$i  .'" maxlength="100" /></td>
                                                        <td align="center"><input class="uploadform" type="text" name="comment_'.$i.'" id="comment_'.$i.'" maxlength="100" /></td>
                                                        <td align="center"><input class="uploadform" type="file" name="myfile'.$i  .'" id="file_'.$i.'">             </input></td>
                                                     </tr>';
                                            echo $output;
                                    }
                                ?>
                                </tbody>
                            </table>
                        <br />
                        <input type="text" id="maxfiles"  name="maxfiles"   value="<?php echo $maxfiles ; ?>" />
                        <input type="text" id="ia"        name="ia"         value="<?php echo $ia ;       ?>" />
                        <input type="text" id="json"      name="json"       value="" />
                        <input type="text" id="filecount" name="filecount"  value=0  />

                        <label><input type="submit" value="Upload" /></label>
                        <br /><br />
                    </div>

                    <iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
                </form>

            </div>

            <div id="tabs-2">
                <p>Upload from URL - Coming Soon !</p>
            </div>

            <!-- Gallery Tab -->
            <div id="tabs-3">
                <table border="0" cellpadding="10" cellspacing="10" align="center" width="100%">
                    <?php
                    $output = '';
                        for ($i = 0; $i < $maxfiles; $i++)
                        {
                            $output .= '
                                <tr>
                                    <td><label>Title</label></td>
                                    <td><input type="text" id="gallery_title_'.$i.'" maxlength="100" /><br /></td>
                                    <td rowspan="3"><img id="gallery_image_'.$i.'" height="200" width="200" src="" /></td>
                                </tr>
                                <tr>
                                    <td><label>Comment</label></td>
                                    <td><input type="text" id="gallery_comment_'.$i.'" maxlength="100" /></td>
                                </tr>
                                <tr>
                                    <td><button type="button" onClick="deletefile('.$i.')">Delete</button>
                                </tr>
                                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
                        }
                        echo $output;
                    ?>
                </table>
                <button id="savechanges">Save Changes</button>
            </div>
            <!-- TODO-FUQT: Add a Save and Exit Button
                <p align="center"><button id="saveandexit">Save and Exit</button></p>
            -->
        </div>

    </body>
</html>
