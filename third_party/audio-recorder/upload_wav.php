<?php
    // Get the file and upload it to the directory. 
    $tmp_filename = $_FILES['output_wav']['tmp_name'];
    $tmp_size = 0.001 * $_FILES['output_wav']['size'];
    $root_dir = getcwd() . '/../../';
    $upload_dir = $root_dir . 'tmp/upload/'; 
    $filename = uniqid('rec_' . $_POST['qid'] . '_') . '.wav';
    move_uploaded_file($tmp_filename, $upload_dir . $filename);
    
    // Return the uploaded file specifics as a JSON representation
    $json_array = array(
            'size'      => $tmp_size, 
            'name'      => 'recording.wav',
            'filename'  => $filename,
            'ext'       => 'wav',
        ); 
    echo '[' . json_encode($json_array) . ']';
    