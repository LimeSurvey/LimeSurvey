<?php


    $filename = "upload/tmp/".$_GET['file'];

    $fh = fopen($filename, 'w') or die("can't open file");
    fclose($filename);

    if (unlink($filename))
        echo 'File '.$filename.' deleted';
    else
        echo 'Oops, There was an error deleting the file';
    
?>