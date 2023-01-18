<?php
/**
 * @var array $dropdownItems
 */
?>
<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary ls-dropdown-toggle" data-bs-toggle="dropdown" type="button"
            aria-expanded="false">
        ...
    </button>
    <ul class="dropdown-menu">
        <?php foreach ($dropdownItems as $dropdownItem) : ?>
            <li>
                <a class="dropdown-item <?= $dropdownItem['enabledCondition'] ? "" : "disabled" ?>"
                   href="<?= $dropdownItem['url'] ?>"
                   role="button"
                   data-bs-toggle="tooltip"
                   title="<?= $dropdownItem['title'] ?>">
                    <i class="ri-add-circle-fill"></i><?= $dropdownItem['title'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php
App()->getClientScript()->registerScriptFile(
    "/application/extensions/admin/grid/assets/action_dropdown.js",
    CClientScript::POS_END
);
?>
