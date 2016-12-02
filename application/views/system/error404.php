<h1>404 Not found.</h1>
<?php
    echo CHtml::tag('h2', array(), nl2br(CHtml::encode($error['message'])));
?>
<p>
    The requested URL was not found on this server.
    If you entered the URL manually please check your spelling and try again.
</p>
<p>
    If you think this is a server error, please contact <?php echo $admin; ?>.
</p>
