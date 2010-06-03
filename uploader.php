<html>
    <head>
        <link rel="stylesheet" href="uploader-style.css" type="text/css" />
        <script type="text/javascript" src="scripts/jquery/jquery.js"></script>
        <script src="scripts/jquery/jquery.sTabs.min.js" type="text/javascript"></script>

        <script type="text/javascript">

            $(document).ready(function(){
                $(function(){
                    $('#default').sTabs();
                    $('#options').sTabs({animate:true, duration:400, startWith: 2});
                });

                $('#f1_result').hide();
                $('#f1_upload_process').hide();
            });

            function startUpload(){
                $('#f1_result').hide();
                $('#f1_upload_form').hide();
                $('#f1_upload_process').show();
                return true;
            }

            function stopUpload(success){
                var result = '';
                if (success == 1){
                     result = '<span>The file was uploaded successfully!<\/span><br/><br/>';
                }
                else {
                     result = '<span>There was an error during file upload!<\/span><br/><br/>';
                }
                $('#f1_result').html(result);
                $('#f1_result').show();
                $('#f1_upload_process').hide();
                $('#f1_upload_form').show();
                <!-- // set the value of file input boxes to blank -->
                  return true;
            }
        </script>

    </head>

    <body bgcolor="white">


        <ul id="default">
            <li><a href="#def-first">From Computer</a></li>
            <li><a href="#def-second">From URL</a></li>
            <li><a href="#def-third">Gallery</a></li>
        </ul>

        <div id="def-first">


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
                                    <input class="text" type="text" name="'.$ia.'_title_'.$i
                                    .'" id="answer'.$ia.'_title_'.$i.'" maxlength="100" />
                                </td>
                                <td align="center">
                                    <input type="textarea" name="'.$ia.'_comment_'.$i
                                    .'" id="answer'.$ia.'_comment_'.$i.'" maxlength="100" />
                                </td>
                                <td align="center">
                                    <input class="file" type="file" name="myfile'.$i.'" ></input>
                                </td>
                                </tr>';
                    echo $output;
            }
        ?>
                        </tbody>
                    </table>
                    
                 <br />
                 <?php echo "<input type='hidden' name='maxfiles' value='".$maxfiles."'></input>"; ?>
                 <label><input type="submit" value="Upload" /></label>
                 <br /><br />
                 </div>

                 <iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
            </form>


        </div>

        <div id="def-second">
            <p>Upload from URL - Coming Soon !</p>
        </div>

        <div id="def-third">
            <p>Gallery - Coming Soon !</p>
        </div>

    </body>
</html>