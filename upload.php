<?php
    /* Save the uploaded files in upload/tmp/ folder
     * get the inputs and process them and to form a JSON string
     * this JSON string also contains the filenames of the uploaded files
     * Send this JSON string to the survey page and use jQuery to
     * set the attribute of the fuqt question to this JSON string
     * Also, pass on the filecount to the survey page where it will set the
     * filecount to this filecount
     * When the upload is called second time, there might be already some files
     * that would have been uploaded in the first go, hence, we need to take
     * special care of the JSON string and filecount.
     * Therefore, while processing the JSON string, check to see if already
     * something exists. If nothing exists, parse the JSON string to a PHP array
     * and append the new files uploaded.
     * Also check the filecount and add to it if it is not zero/NULL
     */

    // Edit upload location here

    $maxfiles = $_POST['maxfiles'];
   
    $destination_path = getcwd().DIRECTORY_SEPARATOR."upload/tmp/";
    $result = 0;

    for ($i = 1; $i <= $maxfiles; $i++)
    {
        $myfile = 'myfile'.$i;
        if ($_FILES[$myfile]['tmp_name'] == NULL)
            break;

        $target_path = $destination_path . basename( $_FILES[$myfile]['name']);

        if (move_uploaded_file($_FILES[$myfile]['tmp_name'], $target_path))
           $result = 1;
        else
        {
            $result = 0;
            break;
        }
    }

    sleep(1);
?>

<script language="javascript" type="text/javascript">window.parent.window.stopUpload(<?php echo $result ; ?>);</script>
