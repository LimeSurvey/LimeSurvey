<?php
    $tmp_filename = $_FILES['output_wav']['tmp_name'];
    $root_dir = getcwd() . '/../../';
    $upload_dir = $root_dir . 'upload/surveys/' . $_POST['sid'] . '/files/'; 
    move_uploaded_file($tmp_filename, $upload_dir . $_POST['qid'] . '.wav');
    