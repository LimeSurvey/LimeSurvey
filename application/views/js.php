<?php

/*
 * This view file will wrap the passed data in a javascript object.
 */
$json = json_encode($data, JSON_PRETTY_PRINT +  JSON_FORCE_OBJECT);
echo "var LS = $json";
?>
