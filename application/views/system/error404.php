<html>
    <head>
        <title>Error</title>
    </head>
    <body style="width: 1024px; margin: auto;">
        <h1>404 Not found.</h1>
        <?php
            echo CHtml::tag('h2', array(), $data['message']);

            echo "Please contact " . App()->getConfig('siteadminname') . " (" . App()->getConfig('siteadminemail') . ") for further assistance.";
        ?>
    </body>
</html>