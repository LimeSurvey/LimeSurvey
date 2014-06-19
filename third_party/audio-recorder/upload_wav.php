<?php
    // Get the file and upload it to the directory. 
    $tmp_filename = $_FILES['output_wav']['tmp_name'];
    $tmp_size = 0.001 * $_FILES['output_wav']['size'];
    $root_dir = getcwd() . '/../../';
    $upload_dir = $root_dir . 'upload/surveys/' . $_POST['sid'] . '/files/'; 
    $filename = 'rec_' . $_POST['qid'] . '_' . uniqid() . '.wav';
    move_uploaded_file($tmp_filename, $upload_dir . $filename);
    
    // Return the uploaded file specifics as a JSON representation
    $json_array = array(
            'size'      => $tmp_size, 
            'name'      => 'recording.wav',
            'filename'  => $filename,
            'ext'       => 'wav',
        ); 
    echo '[' . json_encode($json_array) . ']';
    