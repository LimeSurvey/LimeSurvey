<?php
$exportCsvUrl = App()->createUrl("userManagement/exportUser", ["outputFormat" => "csv"]);
$exportJsonUrl = App()->createUrl("userManagement/exportUser", ["outputFormat" => "json"]);
?>
<ul class="dropdown-menu">
    <li>
        <button
            type="button"
            class="dropdown-item"
            onclick='window.location.href = <?= json_encode($exportCsvUrl) ?>'
        ><?php eT("CSV") ?></button>
    </li>
    <li>
        <button
            type="button"
            class="dropdown-item"
            onclick='window.location.href = <?= json_encode($exportJsonUrl) ?>'
        ><?php eT("JSON") ?></button>
    </li>
</ul>
