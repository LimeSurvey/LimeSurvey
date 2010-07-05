<?php

    $uploaddir = 'upload/tmp/';
    $file = $uploaddir . basename($_FILES['uploadfile']['name']);
    $size=$_FILES['uploadfile']['size'];
    $valid_extensions = $_POST['valid_extensions'];

    $valid_extensions_array = explode(",", $valid_extensions);

    $pathinfo = pathinfo(basename($file));
    $ext = $pathinfo['extension'];

    // TODO: use the size from configuration/settings
    if($size > 1024000)
    {
        $return = array(
                        "success" => false,
                        "msg" => "The file is too large"
                    );

        unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    else if (!in_array($ext, $valid_extensions_array))
    {
        $return = array(
                        "success" => false,
                        "msg" => "Sorry, This file extension (".$ext.") is not allowed !".$valid_extensions
                    );

        unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
        $size     = filesize($file);
        $return = array(
                        "success" => true,
                        "size"    => $size,
                        "name"    => basename($file),
                        "ext"     => $ext,
                        "msg"     => "The file has been successfuly uploaded"
                    );
        echo json_encode($return);
    } else {
        $return = array(
                        "success" => false,
                        "msg" => "Unknown error"
                    );
        echo json_encode($return);
    }
?>