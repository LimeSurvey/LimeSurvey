<?php

    $uploaddir = 'upload/tmp/';
    $file = $uploaddir . basename($_FILES['uploadfile']['name']);
    $size=$_FILES['uploadfile']['size'];
    $valid_extensions = $_POST['valid_extensions'];
    $maxfilesize = $_POST['maxfilesize'];
    $preview = $_POST['preview'];

    $valid_extensions_array = explode(",", $valid_extensions);

    $pathinfo = pathinfo(basename($file));
    $ext = $pathinfo['extension'];

    // check to ensure that the file does not cross the maximum file size
    if($size > $maxfilesize)
    {
        $return = array(
                        "success" => false,
                        "msg" => "Sorry, This file is too large. Only files upto ".$maxfilesize." KB are allowed"
                    );

        unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    // check to see that this file type is allowed
    // it is also  checked at the client side, but jst double checking
    else if (!in_array($ext, $valid_extensions_array))
    {
        $return = array(
                        "success" => false,
                        "msg" => "Sorry, This file extension (".$ext.") is not allowed !".$valid_extensions
                    );

        unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    // if everything went fine and the file was uploaded successfuly,
    // send the file related info back to the client
    else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
        $size     = filesize($file);
        $return = array(
                        "success" => true,
                        "size"    => $size,
                        "name"    => basename($file),
                        "ext"     => $ext,
                        "msg"     => "The file has been successfuly uploaded"
                    );
        if ($preview == 0)
            unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    // if there was some error, report error message
    else {
        $return = array(
                        "success" => false,
                        "msg" => "Unknown error"
                    );
        echo json_encode($return);
    }
?>