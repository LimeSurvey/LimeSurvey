<?php
?>

<ul class="dropdown-menu">
    <li>
        <?= CHtml::link(gT("CSV"), App()->createUrl("userManagement/exportUser", ["outputFormat" => "csv"]), ["class" => "dropdown-item"]); ?>
    </li>
    <li>
        <?= CHtml::link(gT("JSON"), App()->createUrl("userManagement/exportUser", ["outputFormat" => "json"]), ["class" => "dropdown-item"]); ?>
    </li>
</ul>
