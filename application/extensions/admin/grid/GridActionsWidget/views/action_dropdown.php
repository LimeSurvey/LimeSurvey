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
                <div data-bs-toggle="tooltip" title="<?= $dropdownItem['tooltip'] ?? '' ?>">
                    <a id="<?= $dropdownItem['linkId'] ?? '' ?>"
                       class="dropdown-item <?= $dropdownItem['enabledCondition'] ? "" : "disabled" ?> <?= $dropdownItem['linkClass'] ?? '' ?>"
                       href="<?= $dropdownItem['url'] ?? '#' ?>"
                       role="button"
                        <?php if (isset($dropdownItem['linkAttributes']) && is_array($dropdownItem['linkAttributes'])) : ?>
                            <?php foreach ($dropdownItem['linkAttributes'] as $attribute => $value) : ?>
                                <?= "$attribute='$value'" ?>
                            <?php endforeach; ?>
                        <?php endif; ?>>
                        <?php if (isset($dropdownItem['iconClass'])) : ?>
                            <i class="<?= $dropdownItem["iconClass"] ?>"></i>
                        <?php endif; ?>
                        <?= $dropdownItem['title'] ?>
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>