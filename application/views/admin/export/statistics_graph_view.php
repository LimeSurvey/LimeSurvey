{
    "ok": <?php echo $success ?>
    
    <?php
    if (isset($mapdata)) {
        echo ",\"mapdata\":".json_encode($mapdata);
    }
    ?>
    
    <?php
    if (isset($chartdata)) {
        echo ",\"chartdata\":".json_encode($chartdata);
    }
    ?>
}
