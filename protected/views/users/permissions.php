<div class="col-sm-12 col-md-6 col-md-offset-3">
<?php
/** @var \ls\pluginmanager\iAuthorizationPlugin $plugin */
$rows = [];

foreach ($permissions as $base => $details) {
    $row = [
        'icon',
        'permission' => $base,
        'all',
    ];
    foreach (['create', 'read', 'update', 'delete', 'import', 'export'] as $crud) {
        if (!$details[$crud]) {
            $row[$crud] = null;
        } else {
            $row[$crud] = $plugin->isAssigned("$base.$crud", $userId);
        }

    }

    $rows[] = $row;

}
echo '<pre>';
var_dump($rows);

?>
</div>