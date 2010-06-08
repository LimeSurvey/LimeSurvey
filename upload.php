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

    $maxfiles       = $_POST['maxfiles'];
    $ia             = $_POST['ia'];
    $json           = $_POST['json'];
    $filecount      = $_POST['filecount'];

    $destination_path = getcwd().DIRECTORY_SEPARATOR."upload/tmp/";
    $result = 0;

    if (!$json || $json != '')
        $phparray = json_decode($json);
    else
        $phparray[] = array();

    for ($i = 1; $i <= $maxfiles; $i++)
    {
        $myfile  = 'myfile'.$i;
        $title   = 'title_'.$i;
        $comment = 'comment_'.$i;
        
        if ($_FILES[$myfile]['tmp_name'] == NULL)
            continue;

        $target_path = $destination_path . basename( $_FILES[$myfile]['name']);

        if (move_uploaded_file($_FILES[$myfile]['tmp_name'], $target_path))
        {
            $result = 1;
            $filecount += 1;

            //TODO-FUQT: randomize filenames !
            $phparray[] = array(
                "title" => "$_POST[$title]",
                "comment" => "$_POST[$comment]",
                "filename" => basename($target_path)
            );
        }
        else
        {
            $result = 0;
            break;
        }
    }
    
    $json = json_encode($phparray);

    sleep(1);
?>

<script language="javascript" type="text/javascript">
    
    var result      =  <?php echo $result       ; ?>;
    var filecount   =  <?php echo $filecount    ; ?>;
    var json        =  <?php echo $json         ; ?>;
    var ia          = '<?php echo $ia           ; ?>';
    var maxfiles    =  <?php echo $maxfiles     ; ?>;
    
    window.parent.window.stopUpload(result, filecount, json, ia, maxfiles);
</script>