<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message)
    {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<?php echo $output; ?>
