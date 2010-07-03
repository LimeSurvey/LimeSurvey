<?php

    $uploaddir = 'upload/tmp/';
    $file = $uploaddir . basename($_FILES['uploadfile']['name']);
    $size=$_FILES['uploadfile']['size'];

    // TODO: use the size from configuration/settings
    if($size > 1024000)
    {
        $return = array(
                        "success" => false,
                        "error" => "The file is too large"
                    );

        unlink($_FILES['uploadfile']['tmp_name']);
        echo json_encode($return);
    }
    else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
        $pathinfo = pathinfo(basename($file));
        $size     = filesize($file);
        $return = array(
                        "success" => true,
                        "size" => $size,
                        "name" => basename($file),
                        "ext" => $pathinfo['extension']
                    );
        echo json_encode($return);
    } else {
        $return = array(
                        "success" => false,
                        "error" => "Unknown error"
                    );
        echo json_encode($return);
    }
?>