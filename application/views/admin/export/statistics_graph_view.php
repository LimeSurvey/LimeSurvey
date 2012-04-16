{
    "ok": <?php echo $success ?>
    
    <?php
    if (isset($mapdata)) {
        echo ",\"mapdata\":".ls_json_encode($mapdata);
    }
    ?>
    
    <?php
    if (isset($chartdata)) {
        echo ",\"chartdata\":".ls_json_encode($chartdata);
    }
    ?>
}
