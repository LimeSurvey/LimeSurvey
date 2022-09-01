<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Safe Upload Examples for Arabic Filename</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>
<div class="Paragraph">
<h2 dir="ltr">Safe Upload Examples Output:</h2>
<p><i>PHP5 is not capable of addressing files with multi-byte characters in their names at all (including Arabic language).</i></p>

<?php

if (isset($_POST['submit'])) {
    
    /*
      // Autoload files using Composer autoload
      require_once __DIR__ . '/../vendor/autoload.php';
    */

    include '../src/Arabic.php';
    $Arabic = new \ArPHP\I18N\Arabic();
    
    // Continue only if the file was uploaded via HTTP POST
    if (is_uploaded_file($_FILES['image']['tmp_name'])) {

        // Is file size less than 1 MB = 1,048,576 Byte
        if ($_FILES['image']['size'] < 1048576) {

            // Detect MIME Content-type for a file 
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($_FILES['image']['tmp_name']);
            } else {
                $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['image']['tmp_name']);
            }

            // List of accepted MIME Content-type
            $images = array('image/jpeg', 'image/gif', 'image/png', 'image/svg+xml');

            if (in_array($mime, $images)) {

                // PHP5 is not capable of addressing files with multi-byte characters in their names at all.
                // This is why we use Transliteration functionality in Arabic class
                $filename = trim($Arabic->ar2en($_FILES['image']['name']));

                // Moves an uploaded file to a new location
                // move_uploaded_file ($_FILES['image']['tmp_name'], $dir.DIRECTORY_SEPARATOR.$filename);
                echo "move_uploaded_file(\$_FILES['image']['tmp_name'], \$dir.DIRECTORY_SEPARATOR.\"$filename\");";
            } else {
                echo '<h3>You can upload image file only (i.e. gif, jpg, png, and svg)!</h3>';
            }
        } else {
            echo '<h3>You can not upload file bigger than 1MB!</h3>';
        }
    } else {
        echo '<h3>You have to select file first to upload it!</h3>';
    }
}

?>
<br /><br />

<form  action="safe_upload.php" method="post" enctype="multipart/form-data">

    <input name="image" type="file" size="60">

    <input name="submit" type="submit" value="Upload">

</form>

<h4>Verified Conditions:</h4>
<ol>
    <li>Max uploaded file size is 1 MB</li>
    <li>Accepted MIME Content-types are: image/jpeg, image/gif, image/png, and image/svg+xml</li>
</ol>

</div><br />

<div class="Paragraph">
<h2>Safe Upload Examples Code:</h2>
<?php
$code = <<< END
<?php

if(isset(\$_POST['submit'])){

    \$Arabic = new \\ArPHP\\I18N\\Arabic();
    
    // Continue only if the file was uploaded via HTTP POST
    if (is_uploaded_file(\$_FILES['image']['tmp_name'])) {

        // Is file size less than 1 MB = 1,048,576 Byte
        if (\$_FILES['image']['size'] < 1048576) {

            // Detect MIME Content-type for a file 
            \$mime = mime_content_type(\$_FILES['image']['tmp_name']);
            
            // List of accepted MIME Content-type
            \$images = array('image/jpeg', 'image/gif', 'image/png', 'image/svg+xml');

            if (in_array(\$mime, \$images)) {

                // PHP5 is not capable of addressing files with multi-byte characters in their names at all.
                // This is why we use Transliteration functionality in Arabic class
                \$filename = trim(\$Arabic->ar2en(\$_FILES['image']['name']));
                
                // Moves an uploaded file to a new location
                move_uploaded_file (\$_FILES['image']['tmp_name'], \$dir.DIRECTORY_SEPARATOR.\$filename);
            } else {
                echo '<h3>You can upload image file only (i.e. gif, jpg, png, and svg)!</h3>';
            }
        } else {
            echo '<h3>You can not upload file bigger than 1MB!</h3>';
        }
    } else {
        echo '<h3>You have to select file first to upload it!</h3>';
    }
}

?>

<form  action="safe_upload.php" method="post" enctype="multipart/form-data">

    <input name="image" type="file" size="60">

    <input name="submit" type="submit" value="Upload">

</form><br />
END;

highlight_string($code);
?>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
