<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Advanced File Uploader</title>
  
        <script type="text/javascript" src="scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="scripts/ajaxupload.js"></script>

        <link type="text/css" href="scripts/jquery/css/jquery-ui-1.8.1.custom.css" rel="stylesheet" />
        <link type="text/css" href="scripts/uploader.css" rel="stylesheet" />
        <script type="text/javascript" src="scripts/jquery/jquery-ui.js"></script>
        <script type="text/javascript" src="scripts/uploader.js"></script>
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