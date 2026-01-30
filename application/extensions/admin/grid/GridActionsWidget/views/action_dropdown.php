<?php
/**
 * @var array $dropdownItems
 * @var int $id
 */

?>
<div id="<?= 'dropdown_' . $id; ?>" class="dropdown ls-action_dropdown">
    <button class="ls-dropdown-toggle" data-bs-toggle="dropdown" type="button"
            aria-expanded="false">
        <i class="ri-more-fill"></i>
    </button>
    <ul id="<?= 'dropdownmenu_' . $id; ?>" class="dropdown-menu">
        <?php foreach ($dropdownItems as $dropdownItem) : ?>
            <?php $enabledCondition = $dropdownItem['enabledCondition'] ?? true ?>
            <?php
            if (isset($dropdownItem['submenu']) && $dropdownItem['submenu']) { ?>
                <li class="has-submenu">
                <a href="#" class="dropdown-item d-flex justify-content-between align-items-center"
                   data-bs-toggle="dropdown-submenu" role="button" aria-expanded="false">
                    <?= CHtml::encode($dropdownItem['title']) ?>
                </a>
                <ul class="dropdown-submenu">
                    <?php
                    foreach ($dropdownItem['submenu_items'] as $subItem) { ?>
                        <li>
                            <?php
                            $this->render('dropdown_item',
                                [
                                    'tooltip' => $subItem['tooltip'] ?? '',
                                    'title' => $subItem['title'],
                                    'linkId' => $subItem['linkId'] ?? '',
                                    'linkClass' => $subItem['linkClass'] ?? '',
                                    'url' => $subItem['url'] ?? '#',
                                    'iconClass' => $subItem["iconClass"] ?? null,
                                    'linkAttributes' => $subItem['linkAttributes'] ?? null,
                                    'enabledCondition' => $subItem['enabledCondition'],
                                ]
                            ); ?>
                        </li>
                    <?php }
                    ?>
                </ul>
                <?php
            } else { ?>
                <li>
                <?php $this->render('dropdown_item',
                    [
                        'tooltip' => $dropdownItem['tooltip'] ?? '',
                        'title' => $dropdownItem['title'],
                        'linkId' => $dropdownItem['linkId'] ?? '',
                        'linkClass' => $dropdownItem['linkClass'] ?? '',
                        'url' => $dropdownItem['url'] ?? '#',
                        'iconClass' => $dropdownItem["iconClass"] ?? null,
                        'linkAttributes' => $dropdownItem['linkAttributes'] ?? null,
                        'enabledCondition' => $enabledCondition,
                    ]
                );
            }
            ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
