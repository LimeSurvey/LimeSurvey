<ul class="dropdown-menu">
    <li>
        <?= CHtml::link(
            gT("CSV"),
            App()->createUrl(
                "userManagement/exportUser",
                ["outputFormat" => "csv"]
            ),
            [
                "class" => "dropdown-item",
                "aria-label" => gT("Export users as CSV", "unescaped")
            ]
        ); ?>
    </li>
    <li>
        <?= CHtml::link(
            gT("JSON"),
            App()->createUrl(
                "userManagement/exportUser",
                ["outputFormat" => "json"]
            ),
            [
                "class" => "dropdown-item",
                "aria-label" => gT("Export users as JSON", "unescaped")
            ]
        ); ?>
    </li>
</ul>
