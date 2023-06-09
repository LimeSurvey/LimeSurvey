<?php

?>
<ul class="dropdown-menu" aria-labelledby="example2">
    <li>
        <a
            data-href="<?= $this->createUrl("userManagement/renderUserImport", ["importFormat" => "csv"]) ?>"
            class="dropdown-item UserManagement--action--openmodal"
            data-bs-toggle="modal"
            href="#"> <?php eT("Import (CSV)") ?>
        </a>
    </li>
    <li>
        <a
            data-href="<?= App()->createUrl("userManagement/renderUserImport", ["importFormat" => "json"]) ?>"
            data-bs-toggle="modal"
            class="dropdown-item UserManagement--action--openmodal"
            href="#">
            <?php eT("Import (JSON)"); ?>
        </a>
    </li>
</ul>
