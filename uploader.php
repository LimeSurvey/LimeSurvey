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
            });

            function startUpload(){
                $('#f1_result').hide();
                $('#f1_upload_form').hide();
                $('#f1_upload_process').show();
                return true;
            }

            function stopUpload(success, filecount, json, ia){
                var result = '';
                if (success === 1){
                    result = '<span>The file was uploaded successfully!</span><br/><br/>';
                    $("#"+ia+"_filecount").val(filecount);
                    $("#"+ia).val(JSON.stringify(json));
                    $("input.uploadform").val('');
                }
                else {
                     result = '<span>There was an error during file upload!</span><br/><br/>';
                }
                $('#f1_result').html(result);
                $('#f1_result').show();
                $('#f1_upload_process').hide();
                $('#f1_upload_form').show();

                <!-- // set the value of file input boxes to blank -->
                  return true;
            }

            function passJSON(ia) {
                var jsonstring = $('#'+ia).val();
                var filecount  = $('#'+ia+'_filecount').val();
                window.parent.window.copyJSON(jsonstring, filecount);
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
                                    <td align="center">
                                        <input class="uploadform" type="text" name="'.$ia.'_title_'.$i
                                        .'" id="answer'.$ia.'_title_'.$i.'" maxlength="100" />
                                    </td>
                                    <td align="center">
                                        <input type="textarea" class="uploadform" name="'.$ia.'_comment_'.$i
                                        .'" id="answer'.$ia.'_comment_'.$i.'" maxlength="100" />
                                    </td>
                                    <td align="center">
                                        <input  class="uploadform" type="file" name="myfile'.$i.'" ></input>
                                    </td>
                                    </tr>';
                        echo $output;
                }
            ?>
                            </tbody>
                        </table>

                     <br />
                     <?php echo "<input type='text' class='maxfiles' id='maxfiles' name='maxfiles' value='".$maxfiles."'></input>"; ?>
                     <?php echo "<input type='text' name='ia' value='".$ia."'></input>"; ?>
                     <?php echo "<input type='text' id='".$ia."' name='json' value=''></input>"; ?>
                     <?php echo "<input type='text' id='".$ia."_filecount' name='filecount' value=0></input><br>"; ?>

                     <label><input type="submit" value="Upload" /></label>
                     <br /><br />
                     </div>

                     <iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
                </form>

            </div>

            <div id="tabs-2">
                <p>Upload from URL - Coming Soon !</p>
            </div>

            <div id="tabs-3">
                <p>Gallery - Coming Soon !</p>
            </div>
            
        </div>

    </body>
</html>